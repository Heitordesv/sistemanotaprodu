<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function publicKey()
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'content_encoding' => 'nullable|string|max:30',
        ]);

        $session = session('user_logged');
        if (!$session || empty($session['id'])) {
            abort(401);
        }

        $isSuper = (bool) ($session['super'] ?? false);
        $empresaId = $session['empresa'] ?? null;
        $endpoint = $request->endpoint;

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $endpoint)],
            [
                'usuario_id' => (int) $session['id'],
                'empresa_id' => $empresaId ? (int) $empresaId : null,
                'is_super' => $isSuper,
                'endpoint' => $endpoint,
                'p256dh' => $request->input('keys.p256dh'),
                'auth' => $request->input('keys.auth'),
                'content_encoding' => $request->input('content_encoding', 'aes128gcm'),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'id' => $subscription->id,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('endpoint_hash', hash('sha256', $request->endpoint))->delete();

        return response()->json(['success' => true]);
    }
}