<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 — Contrato: api-contracts.md (NUNCA cambiar en silencio)
|--------------------------------------------------------------------------
| Todas las respuestas envueltas en "data". Precios como string.
| Los endpoints del catálogo llegan en la Fase 1; por ahora, ping
| para validar el stack completo (CORS, deploy, etc.).
*/

Route::prefix('v1')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'data' => [
                'message' => 'pong',
                'time' => now()->toIso8601String(),
            ],
        ]);
    });
});
