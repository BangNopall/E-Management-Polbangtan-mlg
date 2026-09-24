<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Escape spreadsheet formula injection characters (=, +, -, @, tab, CR).
     *
     * @param string|null $value
     * @return string|null
     */
    public static function escapeFormula(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $dangerousChars = ['=', '+', '-', '@', "\t", "\r"];

        if (in_array($value[0], $dangerousChars, true)) {
            return "'" . $value;
        }

        return $value;
    }
}
