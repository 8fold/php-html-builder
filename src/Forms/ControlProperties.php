<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

final class ControlProperties
{
    /**
     * @param list<string> $inputProperties
     * @return list<string>
     */
    public static function withWarning(
        array $inputProperties,
        string $warningId
    ): array {
        $properties = [];
        $descriptionIds = [];

        $ariaProperty = 'aria-describedby ';
        foreach ($inputProperties as $property) {
            if (str_starts_with($property, $ariaProperty)) {
                $ids = preg_split(
                    '/\s+/',
                    trim(substr($property, strlen($ariaProperty)))
                );

                foreach ($ids === false ? [] : $ids as $id) {
                    if ($id !== '') {
                        $descriptionIds[] = $id;
                    }
                }
            } else {
                $properties[] = $property;
            }
        }

        $descriptionIds[] = $warningId;

        $properties[] = 'aria-invalid true';
        $properties[] = $ariaProperty . implode(' ', array_unique($descriptionIds));

        return $properties;
    }
}
