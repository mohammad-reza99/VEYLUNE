<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class ProductReadinessAuditHarness
{
    /**
     * @param array<string, array<string, mixed>> $candidate
     *
     * @return array<string, list<string>>
     */
    public static function missingRequirements(array $candidate): array
    {
        $missing = [];

        foreach (ProductReadinessContract::reviewRequirements() as $domain => $requirements) {
            foreach ($requirements as $requirement) {
                if (!isset($candidate[$domain][$requirement]) || $candidate[$domain][$requirement] === '') {
                    $missing[$domain][] = $requirement;
                }
            }
        }

        return $missing;
    }

    /**
     * @param array<string, array<string, mixed>> $candidate
     */
    public static function isReadyForPublicationReview(array $candidate): bool
    {
        return self::missingRequirements($candidate) === [];
    }
}
