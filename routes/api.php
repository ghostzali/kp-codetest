<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\MedicineOutgoingController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/issue-token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return response()->json(['token' => $user->createToken($request->email)->plainTextToken]);
});

Route::get('/user', function (Request $request) {
    try {
        return $request->user();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'detail' => $e
        ], 401);
    }
})->middleware('auth:sanctum');

Route::prefix('/v1/{lang}')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('/medicine-outgoing')->controller(MedicineOutgoingController::class)->group(function () {
            Route::get('', 'index');
            Route::get('/{id}', 'show');
        });
    });
});
