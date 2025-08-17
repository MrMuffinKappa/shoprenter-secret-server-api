<?php

namespace App\Http\Controllers;

use App\Models\Secret; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Str; 
use Carbon\Carbon; 

use Illuminate\Support\Facades\Log;


class SecretController extends Controller
{
    /**
     * Új titok tárolása
     * Ez a funkció kezeli le az üzenetek validálását, tárolását és a választ.
     */
    public function store(Request $request)
    {
        //Üzenet validálása hogy megfelel-e a követelményeknek
        $validator = Validator::make($request->all(), [
            'secret' => 'required|string',
            'expireAfterViews' => 'required|integer|min:1',
            'expireAfter' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 405);
        }

        $validated = $validator->validated();

        $expiresAt = null;
        if ($validated['expireAfter'] > 0) {
            $expiresAt = Carbon::now()->addMinutes($validated['expireAfter']);
        }

        try {
            // Üzenet tárolása és hash generálása
            $secret = Secret::create([
                'hash' => Str::random(12),
                'secretText' => $validated['secret'],
                'remainingViews' => $validated['expireAfterViews'],
                'expiresAt' => $expiresAt,
            ]);
        } catch (\Throwable $e) {
            // Visszaküldünk egy értelmes hibaüzenetet ahelyett, hogy összeomlanánk
            return response()->json([
                'error' => 'Szerver oldali hiba történt az adatbázisba íráskor.',
                'message' => $e->getMessage(),
            ], 500);
        }

        // Válasz összerakása és elküldése
        $responseData = [
            'hash' => $secret->hash,
            'secretText' => $secret->secretText,
            'createdAt' => $secret->created_at->toIso8601String(),
            'expiresAt' => $secret->expiresAt ? $secret->expiresAt->toIso8601String() : null,
            'remainingViews' => $secret->remainingViews,
        ];

        return $this->formatResponse($request, $responseData, 201);
    }

    /**
     * Üzenet mutatása
     * Ez a funkció felel azért hogy ha egy hash-el hívjuk meg az API-t akkor ellenörzi annak a feltételeit és ha mindennek megfelel akkor visszaküldi az üzenetet.
     */
    public function show(Request $request, string $hash)
    {
        // Hash keresése a db-ben
        $secret = Secret::where('hash', $hash)->first();

        // Validálás (létezik-e, van-e még megtekintés rajta, érvényes-e még)
        if (!$secret) {
            return $this->formatResponse($request, ['error' => 'Secret not found'], 404);
        }

        if ($secret->remainingViews <= 0) {
            return $this->formatResponse($request, ['error' => 'Secret not found (no views left)'], 404);
        }

        if ($secret->expiresAt && $secret->expiresAt->isPast()) {
            return $this->formatResponse($request, ['error' => 'Secret not found (expired)'], 404);
        }

        // A hátralévő megtekintések számának csökkentése
        $secret->remainingViews--;
        $secret->save();

        // Válasz formázása és elküldése
        $responseData = [
            'hash' => $secret->hash,
            'secretText' => $secret->secretText,
            'createdAt' => $secret->created_at->toIso8601String(),
            'expiresAt' => $secret->expiresAt ? $secret->expiresAt->toIso8601String() : null,
            'remainingViews' => $secret->remainingViews,
        ];

        return $this->formatResponse($request, $responseData);
    }
    
    /**
     * Segéd funkció a formázáshoz
     * ha JSON helyett XML formában kell a választ visszaküldeni akkor ez a funkció kezeli azt le.
     */
    private function formatResponse(Request $request, array $data, int $status = 200)
    {
        $format = $request->prefers(['application/json', 'application/xml']);

        switch ($format) {
            case 'application/xml':
                return response()->xml($data, $status);
            case 'application/json':
            default:
                return response()->json($data, $status);
        }
    }
}