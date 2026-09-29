<?php declare(strict_types=1);

namespace VeyluneTheme\Testing;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\RefundPaymentTransactionStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransactionCaptureRefund\OrderTransactionCaptureRefundStateHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class LocalPaymentHandler extends AbstractPaymentHandler
{
    public function __construct(
        private readonly Connection $db,
        private readonly OrderTransactionStateHandler $states,
        private readonly OrderTransactionCaptureRefundStateHandler $refundStates,
        private readonly string $environment
    ) {}

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return $type === PaymentHandlerType::REFUND
            && $paymentMethodId === LocalCommerce::PAYMENT
            && $this->environment === 'dev'
            && getenv('DDEV_PROJECT') === 'veylune-shopware';
    }

    public function pay(Request $request, PaymentTransactionStruct $transaction, Context $context, ?Struct $validateStruct): ?RedirectResponse
    {
        $id = $transaction->getOrderTransactionId();
        $row = $this->db->fetchAssociative('SELECT LOWER(HEX(o.sales_channel_id)) channel, LOWER(HEX(t.payment_method_id)) payment FROM order_transaction t JOIN `order` o ON o.id = t.order_id AND o.version_id = t.order_version_id WHERE t.id = UNHEX(:id)', ['id' => $id]);
        if ($this->environment !== 'dev'
            || getenv('DDEV_PROJECT') !== 'veylune-shopware'
            || !LocalCommerce::isTestCheckoutChannel((string) ($row['channel'] ?? ''))) {
            throw PaymentException::syncProcessInterrupted($id, 'Local test payments are unavailable in this environment.');
        }
        if ($row['payment'] === LocalCommerce::DECLINED) {
            throw PaymentException::syncProcessInterrupted($id, 'Test payment declined. No charge was made.');
        }
        if ($row['payment'] !== LocalCommerce::PAYMENT) {
            throw PaymentException::syncProcessInterrupted($id, 'Unknown test payment method.');
        }
        $this->states->paid($id, $context);
        return null;
    }

    public function refund(RefundPaymentTransactionStruct $transaction, Context $context): void
    {
        $refundId = $transaction->getRefundId();
        $transactionId = $transaction->getOrderTransactionId();
        $row = $this->db->fetchAssociative(
            'SELECT LOWER(HEX(o.sales_channel_id)) channel, LOWER(HEX(t.payment_method_id)) payment
             FROM order_transaction_capture_refund r
             JOIN order_transaction_capture c ON c.id = r.capture_id
             JOIN order_transaction t ON t.id = c.order_transaction_id AND t.version_id = c.order_transaction_version_id
             JOIN `order` o ON o.id = t.order_id AND o.version_id = t.order_version_id
             WHERE r.id = UNHEX(:refund) AND t.id = UNHEX(:transaction)',
            ['refund' => $refundId, 'transaction' => $transactionId]
        );
        if ($this->environment !== 'dev' || getenv('DDEV_PROJECT') !== 'veylune-shopware'
            || !LocalCommerce::isTestCheckoutChannel((string) ($row['channel'] ?? ''))
            || ($row['payment'] ?? '') !== LocalCommerce::PAYMENT) {
            throw PaymentException::refundInterrupted($refundId, 'Local test refunds are unavailable for this transaction.');
        }
        $this->refundStates->complete($refundId, $context);
        $this->states->refund($transactionId, $context);
    }
}
