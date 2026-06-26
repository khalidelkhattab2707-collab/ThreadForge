<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CampaignBlueprintController;
use App\Http\Controllers\Api\RawContentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeneratedPostController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
 Route::get('/user', function (Request $request) {
    return $request->user();




})->middleware('auth:sanctum');
 Route::middleware('auth:sanctum')->group(function(){
Route::post('campaing-blueprint/store',[CampaignBlueprintController::class,'store']);
Route::get('campaing-blueprint/',[CampaignBlueprintController::class,'index']);
Route::get('campaing-blueprint/{campaignBlueprint}',[CampaignBlueprintController::class,'show']);
Route::put('campaing-blueprint/{campaignBlueprint}',[CampaignBlueprintController::class,'update']);
Route::delete('campaing-blueprint/{campaignBlueprint}',[CampaignBlueprintController::class,'delete']);


Route::post('raw-content/store',[RawContentController::class,'store']);

Route::post('generated-posts/store',[GeneratedPostController::class,'store']);
Route::get('generated-posts/',[GeneratedPostController::class,'index']);
Route::get('/generated-posts/{id}', [GeneratedPostController::class, 'show']);
Route::put('generated-posts/{generatedpost}',[GeneratedPostController::class,'update']);
Route::delete('generated-posts/{generatedpost}',[GeneratedPostController::class,'delete']);
 });


