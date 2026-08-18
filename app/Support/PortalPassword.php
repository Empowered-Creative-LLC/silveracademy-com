<?php

namespace App\Support;

use Illuminate\Support\Str;

class PortalPassword
{
    /**
     * Generate a temporary portal password that is safe to put in HTML emails.
     *
     * Laravel's default Str::password() includes symbols such as <, >, and &.
     * Email clients treat those as HTML and truncate what the parent sees
     * (e.g. a password starting "6^<..." displays as "6^"), so login fails.
     */
    public static function generate(int $length = 12): string
    {
        return Str::password($length, letters: true, numbers: true, symbols: false);
    }
}
