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

    /**
     * Endpoint khusus pengguna yang sudah login (siswa) agar bisa memanggil profiling via sesi web.
     */
    public function showSelf(): JsonResponse
    {
        $userId = auth()->id();
        $siswa = $userId ? Siswa::where('id_user', $userId)->first() : null;
        abort_if(!$siswa, 403);

        return $this->show($siswa);
    }
}
