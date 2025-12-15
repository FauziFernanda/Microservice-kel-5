<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GatewayService;

class GatewayController extends Controller
{
    protected $service;

    public function __construct(GatewayService $service)
    {
        $this->service = $service;
    }

    public function callBoth(Request $request)
    {
        try {
            $userData = $this->service->callUserService($request->correlation_id, $request->auth_token);
            $otherData = $this->service->callOtherService($request->correlation_id, $request->auth_token);

            return response()->json([
                'correlation_id' => $request->correlation_id,
                'userData' => $userData,
                'otherData' => $otherData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'correlation_id' => $request->correlation_id,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
