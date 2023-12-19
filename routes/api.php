<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LeadController;
use App\Http\Controllers\API\AuthController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/leads', [LeadController::class, 'lead_create']);
Route::get('/leadsall', [LeadController::class, 'all_lead']);
Route::get('/singleleads/{id}', [LeadController::class, 'single_lead']);
Route::get('/age', [LeadController::class, 'age']);
Route::post('/lead_update/{id}', [LeadController::class, 'lead_update']);
Route::get('/lead_delete/{id}', [LeadController::class, 'lead_delete']);
Route::post('/comments', [LeadController::class, 'message_create']);
Route::get('/date_shedule/{id}', [LeadController::class, 'schedule_date_send']);
Route::get('/shedule_date', [LeadController::class, 'shedule_date']);
Route::get('/message_get/{id}', [LeadController::class, 'message_get']);
Route::get('/scheduled_lead', [LeadController::class, 'scheduled_all_lead']);
Route::post('/lead_category/{id}', [LeadController::class, 'lead_category']);
Route::get('/sheduledsingleleads/{id}', [LeadController::class, 'sheduled_single_lead']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'loginUser']);
Route::post('/tagcreate/{id}', [LeadController::class, 'tags_create']);














