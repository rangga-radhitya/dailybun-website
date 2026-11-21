<?php

namespace App\Http\Middleware;

use App\Support\Akses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanAkses
{
    public function handle(Request $request, Closure $next, string $modul): Response
    {
        if (! Akses::boleh($modul)) {
            abort(403, 'Anda tidak memiliki akses ke modul ini.');
        }

        return $next($request);
    }
}
