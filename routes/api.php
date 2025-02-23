<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth')->group(function () {
    Route::post('/user/status', function (Request $request) {
        $user = $request->user();
        $now = now();
        
        $user->update([
            'is_online' => $request->is_online,
            'status' => $request->status ?? ($request->is_online ? 'online' : 'offline'),
            'last_activity' => $now,
            'last_seen' => $now
        ]);

        return response()->json(['status' => 'success']);
    });
});
