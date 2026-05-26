<?php declare(strict_types=1);

namespace VeyluneTheme\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\Aggregate\ProductTranslation\ProductTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use VeyluneTheme\Edition\EditionReferenceRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class EditionMetadataValidationSubscriber implements EventSubscriberInterface
{
    private const ENABLED = 'veylune_edition_enabled';
    private const REFERENCE = 'veylune_edition_reference';
    private const RELEASE_STATE = 'veylune_edition_release_state';
    private const RELEASE_TYPE = 'veylune_edition_release_type';
    private const CONTEXT_FOCUS = 'veylune_edition_context_focus';

    private const REFERENCE_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private const RELEASE_STATES = [
        'active',
        'private_preview',
        'archive_record',
        'selected_works_available',
        'closed',
    ];

    private const RELEASE_TYPES = [
        'atelier_collaboration',
        'material_study',
        'room_edition',
        'small_series',
        'private_preview',
    ];

    private const CONTEXT_FOCUS_VALUES = [
        'supplier_collaboration',
        'material_logic',
        'spatial_logic',
        'cross_collection',
        'archive_continuity',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly EditionReferenceRegistry $editionReferenceRegistry
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'validate',
        ];
    }

    public function validate(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommands() as $command) {
            if (!$command instanceof InsertCommand && !$command instanceof UpdateCommand) {
                continue;
            }

            if ($command->getEntityName() !== ProductTranslationDefinition::ENTITY_NAME) {
                continue;
            }

            $payload = $command->getPayload();

            $incomingCustomFields = $this->extractIncomingCustomFields($payload);

            if ($incomingCustomFields === []) {
                continue;
            }

            $currentCustomFields = $this->fetchCurrentCustomFields($command);
            $customFields = array_replace($currentCustomFields, $incomingCustomFields);

            $violations = $this->validateCustomFields($customFields);

            if ($violations->count() > 0) {
                $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractIncomingCustomFields(array $payload): array
    {
        $customFieldsPayload = $payload['custom_fields'] ?? $payload['customFields'] ?? null;

        if ($customFieldsPayload !== null || \array_key_exists('custom_fields', $payload) || \array_key_exists('customFields', $payload)) {
            return $this->decodeCustomFields($customFieldsPayload);
        }

        $customFields = [];

        foreach ([self::ENABLED, self::REFERENCE, self::RELEASE_STATE, self::RELEASE_TYPE, self::CONTEXT_FOCUS] as $field) {
            if (\array_key_exists($field, $payload)) {
                $customFields[$field] = $payload[$field];
            }
        }

        return $customFields;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeCustomFields(mixed $customFields): array
    {
        if ($customFields === null || $customFields === '') {
            return [];
        }

        if (\is_array($customFields)) {
            return $customFields;
        }

        if (!\is_string($customFields)) {
            return [];
        }

        $decoded = json_decode($customFields, true);

        return \is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCurrentCustomFields(WriteCommand $command): array
    {
        $primaryKey = $command->getPrimaryKey();

        if (!isset($primaryKey['product_id'], $primaryKey['product_version_id'], $primaryKey['language_id'])) {
            return [];
        }

        $customFields = $this->connection->fetchOne(
            <<<'SQL'
                SELECT `custom_fields`
                FROM `product_translation`
                WHERE `product_id` = :productId
                    AND `product_version_id` = :productVersionId
                    AND `language_id` = :languageId
            SQL,
            [
                'productId' => $primaryKey['product_id'],
                'productVersionId' => $primaryKey['product_version_id'],
                'languageId' => $primaryKey['language_id'],
            ]
        );

        return $this->decodeCustomFields($customFields);
    }

    /**
     * @param array<string, mixed> $customFields
     */
    private function validateCustomFields(array $customFields): ConstraintViolationList
    {
        $violations = new ConstraintViolationList();

        if (!$this->hasEditionMetadata($customFields)) {
            return $violations;
        }

        $enabled = $this->isEnabled($customFields[self::ENABLED] ?? false);
        $reference = $this->stringValue($customFields[self::REFERENCE] ?? null);
        $releaseState = $this->stringValue($customFields[self::RELEASE_STATE] ?? null);
        $releaseType = $this->stringValue($customFields[self::RELEASE_TYPE] ?? null);
        $contextFocus = $this->stringValue($customFields[self::CONTEXT_FOCUS] ?? null);

        if ($enabled) {
            $this->requireValue($violations, self::REFERENCE, $reference, 'Edition reference key is required when Edition relationship is enabled.');
            $this->requireValue($violations, self::RELEASE_STATE, $releaseState, 'Edition release state is required when Edition relationship is enabled.');
            $this->requireValue($violations, self::RELEASE_TYPE, $releaseType, 'Edition release type is required when Edition relationship is enabled.');
        }

        if (!$enabled && ($reference !== null || $releaseState !== null || $releaseType !== null || $contextFocus !== null)) {
            $this->addViolation(
                $violations,
                self::ENABLED,
                'Edition relationship must be enabled before Edition metadata fields can be populated.',
                'VEYLUNE_EDITION_METADATA_REQUIRES_ENABLED',
                $customFields[self::ENABLED] ?? null
            );
        }

        if ($reference !== null && !preg_match(self::REFERENCE_PATTERN, $reference)) {
            $this->addViolation(
                $violations,
                self::REFERENCE,
                'Edition reference key must use stable lowercase kebab-case only, for example material-study-travertine-volume-01.',
                'VEYLUNE_EDITION_REFERENCE_FORMAT',
                $reference
            );
        }

        if ($enabled && $reference !== null && $releaseState !== null && preg_match(self::REFERENCE_PATTERN, $reference)) {
            foreach ($this->editionReferenceRegistry->validateRelationship($reference, $releaseState) as $message) {
                $this->addViolation(
                    $violations,
                    self::REFERENCE,
                    $message,
                    'VEYLUNE_EDITION_REFERENCE_RECONCILIATION',
                    $reference
                );
            }
        }

        $this->validateControlledValue($violations, self::RELEASE_STATE, $releaseState, self::RELEASE_STATES);
        $this->validateControlledValue($violations, self::RELEASE_TYPE, $releaseType, self::RELEASE_TYPES);
        $this->validateControlledValue($violations, self::CONTEXT_FOCUS, $contextFocus, self::CONTEXT_FOCUS_VALUES);

        return $violations;
    }

    /**
     * @param array<string, mixed> $customFields
     */
    private function hasEditionMetadata(array $customFields): bool
    {
        foreach ([self::ENABLED, self::REFERENCE, self::RELEASE_STATE, self::RELEASE_TYPE, self::CONTEXT_FOCUS] as $field) {
            if (\array_key_exists($field, $customFields)) {
                return true;
            }
        }

        return false;
    }

    private function requireValue(ConstraintViolationList $violations, string $field, ?string $value, string $message): void
    {
        if ($value !== null) {
            return;
        }

        $this->addViolation($violations, $field, $message, 'VEYLUNE_EDITION_REQUIRED_FIELD', $value);
    }

    /**
     * @param list<string> $allowedValues
     */
    private function validateControlledValue(ConstraintViolationList $violations, string $field, ?string $value, array $allowedValues): void
    {
        if ($value === null || \in_array($value, $allowedValues, true)) {
            return;
        }

        $this->addViolation(
            $violations,
            $field,
            'Edition metadata contains an unsupported controlled value.',
            'VEYLUNE_EDITION_UNSUPPORTED_VALUE',
            $value
        );
    }

    private function stringValue(mixed $value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        return $value === '' ? null : $value;
    }

    private function isEnabled(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

    private function addViolation(ConstraintViolationList $violations, string $field, string $message, string $code, mixed $invalidValue): void
    {
        $violations->add(new ConstraintViolation(
            $message,
            $message,
            [],
            null,
            '/customFields/' . $field,
            $invalidValue,
            null,
            $code
        ));
    }
}
