<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceGatewayController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('services')->middleware('api.token')->group(function () {
    Route::get('/', [ServiceGatewayController::class, 'catalog']);
    Route::get('{service}/{resource}', [ServiceGatewayController::class, 'index']);
    Route::get('{service}/{resource}/{id}', [ServiceGatewayController::class, 'show']);
    Route::post('{service}/{resource}', [ServiceGatewayController::class, 'store']);
    Route::put('{service}/{resource}/{id}', [ServiceGatewayController::class, 'update']);
    Route::delete('{service}/{resource}/{id}', [ServiceGatewayController::class, 'destroy']);
});

foreach (array_keys(config('service_registry.services', [])) as $serviceAlias) {
    Route::prefix($serviceAlias)->middleware('api.token')->group(function () use ($serviceAlias) {
        Route::get('/', [ServiceGatewayController::class, 'serviceCatalog'])->defaults('service', $serviceAlias);
        Route::get('{resource}', [ServiceGatewayController::class, 'aliasIndex'])->defaults('service', $serviceAlias);
        Route::get('{resource}/{id}', [ServiceGatewayController::class, 'aliasShow'])->defaults('service', $serviceAlias);
        Route::post('{resource}', [ServiceGatewayController::class, 'aliasStore'])->defaults('service', $serviceAlias);
        Route::put('{resource}/{id}', [ServiceGatewayController::class, 'aliasUpdate'])->defaults('service', $serviceAlias);
        Route::delete('{resource}/{id}', [ServiceGatewayController::class, 'aliasDestroy'])->defaults('service', $serviceAlias);
    });
}
