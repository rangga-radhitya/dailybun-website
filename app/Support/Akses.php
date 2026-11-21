<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class Akses
{
    public static function boleh(string $modul): bool
    {
        $idrole = (int) (Auth::user()?->idrole ?? 0);

        return in_array($modul, config('akses.'.$idrole, []), true);
    }
}
