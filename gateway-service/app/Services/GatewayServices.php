<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GatewayService
{
    public function callUserService(string $correlationId, ?string $authToken)
    {
        try {
            $response = Http::withHeaders([
                'X-Correlation-ID' => $correlationId,
                'Authorization'   => $authToken,
            ])
                ->timeout(5)
                ->get('http://user-service/api/users');

            if ($response->failed()) {
                throw new Exception(
                    'User service returned ' . $response->status()
                );
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception(
                'User service error: ' . $e->getMessage()
            );
        }
    }

    public function callOtherService(string $correlationId, ?string $authToken)
    {
        try {
            return Http::withHeaders([
                'X-Correlation-ID' => $correlationId,
                'Authorization'   => $authToken,
            ])
                ->timeout(5)
                ->get('http://other-service/api/data')
                ->json();
        } catch (Exception $e) {
            throw new Exception(
                'Other service error: ' . $e->getMessage()
            );
        }
    }
}
