<?php declare(strict_types=1);

namespace VeyluneTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1787846500CleanRemainingLegalCmsPlaceholders extends MigrationStep
{
    /** @var array<string, array{en: array{title: string, body: string}, de: array{title: string, body: string}}> */
    private const PAGES = [
        '019e3bf90c79716c91dcb8e33914c193' => [
            'en' => ['title' => 'Terms of service', 'body' => 'Public commerce terms are in legal review and will be published before orders are enabled. The current storefront is an editorial and private-preview environment; it does not invite or accept public purchase orders.'],
            'de' => ['title' => 'Allgemeine Geschaeftsbedingungen', 'body' => 'Die Bedingungen fuer den oeffentlichen Handel werden rechtlich geprueft und vor der Bestellfreigabe veroeffentlicht. Der aktuelle Storefront ist eine redaktionelle und private Vorschau und nimmt keine oeffentlichen Bestellungen an.'],
        ],
        '019e3bf90c8271f2ab585548d3e747f9' => [
            'en' => ['title' => 'Imprint', 'body' => 'The verified company and responsible-party details for this jurisdiction are being prepared for publication. Public commerce remains disabled until the complete legal disclosure has been reviewed and approved.'],
            'de' => ['title' => 'Impressum', 'body' => 'Die geprueften Unternehmensangaben und Angaben zur verantwortlichen Person werden fuer die Veroeffentlichung vorbereitet. Der oeffentliche Handel bleibt bis zur vollstaendigen rechtlichen Pruefung deaktiviert.'],
        ],
        '019e3bf90c75715ea0fa2f8e9d817ac8' => [
            'en' => ['title' => 'Payment and delivery', 'body' => 'Public payment and delivery options are not active. Verified methods, regions, costs, and delivery estimates will be published before the public order flow is enabled.'],
            'de' => ['title' => 'Zahlung und Lieferung', 'body' => 'Oeffentliche Zahlungs- und Lieferoptionen sind nicht aktiv. Gepruefte Methoden, Regionen, Kosten und Lieferzeiten werden vor der Freigabe des oeffentlichen Bestellprozesses veroeffentlicht.'],
        ],
        '019e3bf90c7c720a946a05f6ab896ec8' => [
            'en' => ['title' => 'Cancellation information', 'body' => 'The jurisdiction-specific cancellation notice will be published with the approved public terms before orders are enabled. No public purchase contract can currently be completed through this storefront.'],
            'de' => ['title' => 'Widerrufsinformation', 'body' => 'Die rechtsraumspezifische Widerrufsbelehrung wird zusammen mit den geprueften Bedingungen vor der Bestellfreigabe veroeffentlicht. Ueber diesen Storefront kann derzeit kein oeffentlicher Kaufvertrag abgeschlossen werden.'],
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1787846500;
    }

    public function update(Connection $connection): void
    {
        foreach (self::PAGES as $pageId => $copy) {
            $rows = $connection->fetchAllAssociative(
                <<<'SQL'
                    SELECT cst.cms_slot_id, cst.cms_slot_version_id, cst.language_id, cst.config, loc.code
                    FROM cms_slot_translation cst
                    INNER JOIN cms_slot slot ON slot.id = cst.cms_slot_id
                    INNER JOIN cms_block block ON block.id = slot.cms_block_id
                    INNER JOIN cms_section section ON section.id = block.cms_section_id
                    INNER JOIN language lang ON lang.id = cst.language_id
                    INNER JOIN locale loc ON loc.id = lang.locale_id
                    WHERE section.cms_page_id = UNHEX(:pageId) AND slot.type = 'text'
                SQL,
                ['pageId' => $pageId]
            );

            foreach ($rows as $row) {
                $locale = str_starts_with((string) $row['code'], 'de-') ? 'de' : 'en';
                $config = json_decode((string) $row['config'], true, 512, JSON_THROW_ON_ERROR);
                $config['content']['value'] = $this->notice($copy[$locale]['title'], $copy[$locale]['body'], $locale);

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
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function notice(string $title, string $body, string $locale): string
    {
        $eyebrow = $locale === 'de' ? 'Rechtliche Information vor dem Launch' : 'Pre-launch legal information';
        $cta = $locale === 'de' ? 'Studio kontaktieren' : 'Contact the studio';

        return sprintf(
            '<div class="veylune-legal-notice"><p class="veylune-legal-notice__eyebrow">%s</p><h1>%s</h1><p>%s</p><p><a href="/private-consultation">%s</a></p></div>',
            $eyebrow,
            $title,
            $body,
            $cta
        );
    }
}
