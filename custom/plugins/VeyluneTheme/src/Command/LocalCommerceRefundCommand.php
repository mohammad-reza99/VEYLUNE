<?php declare(strict_types=1);

namespace VeyluneTheme\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Payment\Cart\PaymentRefundProcessor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use VeyluneTheme\Testing\LocalCommerce;

#[AsCommand(name: 'veylune:test-commerce:refund-latest-cancelled', description: 'Create and process a full local refund for the latest cancelled test order.')]
final class LocalCommerceRefundCommand extends Command
{
    public function __construct(
        private readonly Connection $db,
        private readonly PaymentRefundProcessor $refunds,
        private readonly string $environment
    ) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addOption('apply', null, InputOption::VALUE_NONE, 'Persist and process one local full refund.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->environment !== 'dev' || getenv('DDEV_PROJECT') !== 'veylune-shopware') {
            throw new \RuntimeException('This command is restricted to the Veylune DDEV development project.');
        }
        $row = $this->db->fetchAssociative(
            "SELECT LOWER(HEX(t.id)) transaction_id, t.amount, o.order_number
             FROM `order` o
             JOIN order_transaction t ON t.order_id = o.id AND t.order_version_id = o.version_id
             JOIN state_machine_state os ON os.id = o.state_id
             JOIN state_machine_state ts ON ts.id = t.state_id
             WHERE o.sales_channel_id = UNHEX(:channel)
               AND t.payment_method_id = UNHEX(:payment)
               AND os.technical_name = 'cancelled'
               AND ts.technical_name = 'paid'
             ORDER BY o.created_at DESC LIMIT 1",
            ['channel' => LocalCommerce::CHANNEL, 'payment' => LocalCommerce::PAYMENT]
        );
        if (!$row) { throw new \RuntimeException('No paid cancelled local test order is available.'); }
        $output->writeln(sprintf('Plan: fully refund local test order %s; no external provider is contacted.', $row['order_number']));
        if (!$input->getOption('apply')) { return Command::SUCCESS; }

        $transactionId = (string) $row['transaction_id'];
        $existing = $this->db->fetchOne(
            'SELECT LOWER(HEX(r.id)) FROM order_transaction_capture_refund r JOIN order_transaction_capture c ON c.id=r.capture_id WHERE c.order_transaction_id=UNHEX(:id) LIMIT 1',
            ['id' => $transactionId]
        );
        if ($existing) { throw new \RuntimeException('A refund fixture already exists for this transaction.'); }

        $captureId = Uuid::randomHex();
        $refundId = Uuid::randomHex();
        $captureState = $this->stateId('order_transaction_capture.state', 'completed');
        $refundState = $this->stateId('order_transaction_capture_refund.state', 'open');
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s.v');
        $this->db->transactional(function () use ($captureId, $refundId, $transactionId, $captureState, $refundState, $row, $now): void {
            $this->db->insert('order_transaction_capture', [
                'id' => Uuid::fromHexToBytes($captureId),
                'order_transaction_id' => Uuid::fromHexToBytes($transactionId),
                'order_transaction_version_id' => Uuid::fromHexToBytes('0fa91ce3e96a4bc2be4bd9ce752c3425'),
                'state_id' => Uuid::fromHexToBytes($captureState),
                'external_reference' => 'veylune-local-capture-' . $row['order_number'],
                'amount' => $row['amount'], 'created_at' => $now,
            ]);
            $this->db->insert('order_transaction_capture_refund', [
                'id' => Uuid::fromHexToBytes($refundId),
                'capture_id' => Uuid::fromHexToBytes($captureId),
                'state_id' => Uuid::fromHexToBytes($refundState),
                'reason' => 'Veylune local full-refund lifecycle test',
                'amount' => $row['amount'], 'created_at' => $now,
            ]);
        });
        $this->refunds->processRefund($refundId, Context::createDefaultContext());
        $output->writeln(sprintf('PASS: order %s refund %s completed locally.', $row['order_number'], $refundId));
        return Command::SUCCESS;
    }

    private function stateId(string $machine, string $state): string
    {
        $id = $this->db->fetchOne(
            'SELECT LOWER(HEX(s.id)) FROM state_machine_state s JOIN state_machine m ON m.id=s.state_machine_id WHERE m.technical_name=:machine AND s.technical_name=:state',
            ['machine' => $machine, 'state' => $state]
        );
        if (!$id) { throw new \RuntimeException(sprintf('Missing state %s/%s.', $machine, $state)); }
        return (string) $id;
    }
}
