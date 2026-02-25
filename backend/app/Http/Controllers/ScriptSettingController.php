<?php

namespace App\Http\Controllers;

use App\Models\ScriptSetting;
use App\Models\ScriptStatusLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScriptSettingController extends Controller
{
    /**
     * Return script loading settings for the frontend (is_enabled, script_url).
     */
    public function index(): JsonResponse
    {
        $row = ScriptSetting::where('name', 'external_script')->first();

        return response()->json([
            'is_enabled' => $row ? $row->is_enabled : false,
            'script_url' => $row?->script_url ?? null,
        ]);
    }

    /**
     * Store script status (loaded|failed) from the frontend after script load check.
     * IP and User-Agent from request; script_url from body.
     */
    public function storeStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:loaded,failed',
            'script_url' => 'nullable|string|max:255',
        ]);

        ScriptStatusLog::create([
            'status' => $validated['status'],
            'script_url' => $validated['script_url'] ?? null,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['ok' => true]);
    }
}
