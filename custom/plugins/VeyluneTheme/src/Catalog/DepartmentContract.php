<?php declare(strict_types=1);

namespace VeyluneTheme\Catalog;

final class DepartmentContract
{
    public const DEPARTMENT_FURNITURE = 'furniture';
    public const DEPARTMENT_LIGHTING = 'lighting';
    public const DEPARTMENT_DECOR_OBJECTS = 'decor_objects';
    public const DEPARTMENT_TEXTILES_RUGS = 'textiles_rugs';
    public const DEPARTMENT_DINING_KITCHEN = 'dining_kitchen';
    public const DEPARTMENT_OUTDOOR = 'outdoor';

    private const DEPARTMENTS = [
        self::DEPARTMENT_FURNITURE => ['en' => 'Furniture', 'de' => 'Moebel'],
        self::DEPARTMENT_LIGHTING => ['en' => 'Lighting', 'de' => 'Leuchten'],
        self::DEPARTMENT_DECOR_OBJECTS => ['en' => 'Decor & Objects', 'de' => 'Dekor & Objekte'],
        self::DEPARTMENT_TEXTILES_RUGS => ['en' => 'Textiles & Rugs', 'de' => 'Textilien & Teppiche'],
        self::DEPARTMENT_DINING_KITCHEN => ['en' => 'Dining & Kitchen', 'de' => 'Essen & Kueche'],
        self::DEPARTMENT_OUTDOOR => ['en' => 'Outdoor', 'de' => 'Outdoor'],
    ];

    /**
     * @return array<string, array{en: string, de: string}>
     */
    public static function departments(): array
    {
        return self::DEPARTMENTS;
    }

    public static function isCanonicalDepartment(string $department): bool
    {
        return isset(self::DEPARTMENTS[$department]);
    }
}
