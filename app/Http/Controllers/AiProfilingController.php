<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Services\AiProfilingClient;
use Illuminate\Http\JsonResponse;

class AiProfilingController extends Controller
{
    public function __construct(protected AiProfilingClient $client)
    {
    }

    /**
     * Kembalikan hasil profiling AI dalam bentuk JSON.
     */
    public function show(Siswa $siswa): JsonResponse
    {
        try {
            $profiling = $this->client->profileStudent($siswa);

            return response()->json([
                'success' => true,
                'data' => $profiling,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Profiling AI tidak tersedia: ' . $exception->getMessage(),
            ], 502);
        }
    }
}
