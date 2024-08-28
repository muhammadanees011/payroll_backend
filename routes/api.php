<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HMRCRealTimeInformationController;

Route::get('/example-paye', [HMRCRealTimeInformationController::class, 'examplePaye']);