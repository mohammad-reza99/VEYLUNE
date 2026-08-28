<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1787846400CleanLegacyCmsContent extends MigrationStep
{
    private const PRIVACY_PAGE_ID = '019e3bf90c7f7226a8cb32e3f0ceac6b';
    private const CONTACT_PAGE_ID = '019e3bf907a971b3a48974fb8e7f7fbe';

    public function getCreationTimestamp(): int
    {
        return 1787846400;
    }

    public function update(Connection $connection): void
    {
        $this->replacePrivacyPlaceholder($connection);
        $this->completeContactConfirmation($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function replacePrivacyPlaceholder(Connection $connection): void
    {
        $rows = $connection->fetchAllAssociative(
            <<<'SQL'
                SELECT cst.cms_slot_id, cst.cms_slot_version_id, cst.language_id, cst.config, loc.code
                FROM cms_slot_translation cst
                INNER JOIN cms_slot slot ON slot.id = cst.cms_slot_id
                INNER JOIN cms_block block ON block.id = slot.cms_block_id
                INNER JOIN cms_section section ON section.id = block.cms_section_id
                INNER JOIN language lang ON lang.id = cst.language_id
                INNER JOIN locale loc ON loc.id = lang.locale_id
                WHERE section.cms_page_id = UNHEX(:pageId)
                  AND slot.type = 'text'
            SQL,
            ['pageId' => self::PRIVACY_PAGE_ID]
        );

        foreach ($rows as $row) {
            $config = json_decode((string) $row['config'], true, 512, JSON_THROW_ON_ERROR);
            $isGerman = str_starts_with((string) $row['code'], 'de-');
            $config['content']['value'] = $isGerman ? $this->germanPrivacyNotice() : $this->englishPrivacyNotice();

            $connection->update('cms_slot_translation', [
                'config' => json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
            ], [
                'cms_slot_id' => $row['cms_slot_id'],
                'cms_slot_version_id' => $row['cms_slot_version_id'],
                'language_id' => $row['language_id'],
            ]);
        }
    }

    private function completeContactConfirmation(Connection $connection): void
    {
        $rows = $connection->fetchAllAssociative(
            <<<'SQL'
                SELECT cst.cms_slot_id, cst.cms_slot_version_id, cst.language_id, cst.config, loc.code
                FROM cms_slot_translation cst
                INNER JOIN cms_slot slot ON slot.id = cst.cms_slot_id
                INNER JOIN cms_block block ON block.id = slot.cms_block_id
                INNER JOIN cms_section section ON section.id = block.cms_section_id
                INNER JOIN language lang ON lang.id = cst.language_id
                INNER JOIN locale loc ON loc.id = lang.locale_id
                WHERE section.cms_page_id = UNHEX(:pageId)
                  AND slot.type = 'form'
            SQL,
            ['pageId' => self::CONTACT_PAGE_ID]
        );

        foreach ($rows as $row) {
            $config = json_decode((string) $row['config'], true, 512, JSON_THROW_ON_ERROR);
            $isGerman = str_starts_with((string) $row['code'], 'de-');
            $config['confirmationText'] = [
                'value' => $isGerman
                    ? 'Vielen Dank. Ihre Anfrage ist im Studio eingegangen. Wir melden uns persoenlich bei Ihnen.'
                    : 'Thank you. Your inquiry has reached the studio. We will respond personally.',
                'source' => 'static',
            ];

            $connection->update('cms_slot_translation', [
                'config' => json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'),
            ], [
                'cms_slot_id' => $row['cms_slot_id'],
                'cms_slot_version_id' => $row['cms_slot_version_id'],
                'language_id' => $row['language_id'],
            ]);
        }
    }

    private function englishPrivacyNotice(): string
    {
        return '<div class="veylune-legal-notice"><p class="veylune-legal-notice__eyebrow">Privacy at Veylune</p><h1>Privacy notice</h1><p>We use personal information only to operate the studio, respond to inquiries, and provide requested services. We do not sell personal information.</p><h2>Your choices</h2><p>You may request access, correction, or deletion of information connected with your inquiry. Contact the studio through the private consultation page so the request can be verified and handled securely.</p><h2>Legal review</h2><p>The complete jurisdiction-specific privacy policy is being prepared for publication before public commerce activation. Until that review is complete, no public catalog purchase flow is enabled.</p><p><a href="/private-consultation">Contact the studio about privacy</a></p></div>';
    }

    private function germanPrivacyNotice(): string
    {
        return '<div class="veylune-legal-notice"><p class="veylune-legal-notice__eyebrow">Datenschutz bei Veylune</p><h1>Datenschutzhinweis</h1><p>Wir verwenden personenbezogene Daten nur, um das Studio zu betreiben, Anfragen zu beantworten und angeforderte Leistungen zu erbringen. Wir verkaufen keine personenbezogenen Daten.</p><h2>Ihre Wahlmoeglichkeiten</h2><p>Sie koennen Auskunft, Berichtigung oder Loeschung der mit Ihrer Anfrage verbundenen Daten verlangen. Kontaktieren Sie das Studio ueber die private Beratung, damit Ihre Anfrage sicher geprueft und bearbeitet werden kann.</p><h2>Rechtliche Pruefung</h2><p>Die vollstaendige, rechtsraumspezifische Datenschutzerklaerung wird vor der Aktivierung des oeffentlichen Handels veroeffentlicht. Bis zum Abschluss dieser Pruefung ist kein oeffentlicher Kaufprozess aktiviert.</p><p><a href="/private-consultation">Studio zum Datenschutz kontaktieren</a></p></div>';
    }
}
