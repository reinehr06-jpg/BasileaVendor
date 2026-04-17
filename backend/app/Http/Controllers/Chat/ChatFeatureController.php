<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ChatFeatureController extends Controller
{
    public function status()
    {
        $enabled = (bool) Setting::get('chat_enabled', true);
        
        return response()->json([
            'chat_enabled' => $enabled,
            'message' => $enabled ? 'Chat ativo' : 'Chat desativado'
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        Setting::set('chat_enabled', $request->enabled);

        return response()->json([
            'success' => true,
            'chat_enabled' => $request->enabled,
            'message' => $request->enabled ? 'Chat ativado' : 'Chat desativado'
        ]);
    }
}