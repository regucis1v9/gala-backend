<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SlideController;


    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::post('/login', [UserController::class, 'login']);

    Route::middleware('auth:sanctum')->post('/createFolder', [FolderController::class, 'createFolder']);
    Route::middleware('auth:sanctum')->post('/uploadFiles', [FolderController::class, 'uploadFiles']);
    Route::middleware('auth:sanctum')->post('/deleteFiles', [FolderController::class, 'deleteFiles']);
    Route::middleware('auth:sanctum')->post('/retrieveFiles', [FolderController::class, 'retrieveFiles']);
    Route::get('/listFolders', [FolderController::class, 'listFolders']);
    Route::post('/check-folder/{folder_name}', [FolderController::class, 'checkFolderUnlocked']);
    Route::post('/unlockFolder', [FolderController::class, 'unlockFolder']);

    Route::middleware('auth:sanctum')->get('/addScreen', [ScreenController::class, 'addScreen']);
    Route::middleware('auth:sanctum')->post('/deleteScreen', [ScreenController::class, 'deleteScreen']);
    Route::middleware('auth:sanctum')->get('/getAllScreens', [ScreenController::class, 'getAllScreens']);
    Route::middleware('auth:sanctum')->post('/deleteFolder', [FolderController::class, 'deleteFolder']);

    Route::middleware('auth:sanctum')->post('/createUser', [UserController::class, 'createUser']);
    Route::middleware('auth:sanctum')->post('/editUser', [UserController::class, 'editUser']);
    Route::middleware('auth:sanctum')->get('/getAllUsers', [UserController::class, 'getAllUsers']);
    Route::middleware('auth:sanctum')->post('/deleteUser', [UserController::class, 'deleteUser']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

    Route::middleware('auth:sanctum')->post('/saveSlides', [SlideController::class, 'saveSlides']);
    Route::get('/getSlidesByScreen/{screenID}', [SlideController::class, 'getSlidesByScreen']);
    Route::get('/getTodaysSlides', [SlideController::class, 'getTodaysSlides']);
    Route::get('/getAllSlides', [SlideController::class, 'getAllSlides']);
