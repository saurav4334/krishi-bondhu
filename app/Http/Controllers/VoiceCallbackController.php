<?php

namespace App\Http\Controllers;

use App\Services\ProtiddhoniVoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public webhook the Protiddhoni IVR posts DTMF keypad results to.
 * No auth/session (machine-to-machine) and CSRF-exempt; it only matches on the
 * opaque request_id we generated, and records the pressed key.
 */
class VoiceCallbackController extends Controller
{
    public function handle(Request $request, ProtiddhoniVoiceService $voice): JsonResponse
    {
        // Be tolerant of the various field names a gateway might use.
        $requestId = $request->input('request_id')
            ?? $request->input('requestId')
            ?? $request->input('id');

        $key = $request->input('dtmf_key')
            ?? $request->input('dtmf')
            ?? $request->input('key')
            ?? $request->input('digit');

        if (! $requestId) {
            return response()->json(['ok' => false, 'error' => 'request_id required'], 422);
        }

        $log = $voice->recordDtmf((string) $requestId, $key !== null ? (string) $key : null);

        if (! $log) {
            return response()->json(['ok' => false, 'error' => 'unknown request_id'], 404);
        }

        return response()->json(['ok' => true]);
    }
}
