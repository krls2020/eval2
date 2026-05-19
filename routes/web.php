<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BoardController::class, 'index'])->name('board');
Route::get('/health', [DashboardController::class, 'health']);
Route::get('/status', [DashboardController::class, 'status']);

Route::post('/lists', [BoardController::class, 'storeList'])->name('lists.store');
Route::delete('/lists/{list}', [BoardController::class, 'destroyList'])->name('lists.destroy');

Route::post('/tasks', [BoardController::class, 'storeTask'])->name('tasks.store');
Route::patch('/tasks/{task}', [BoardController::class, 'updateTask'])->name('tasks.update');
Route::delete('/tasks/{task}', [BoardController::class, 'destroyTask'])->name('tasks.destroy');
Route::post('/board/reorder', [BoardController::class, 'reorder'])->name('board.reorder');

Route::get('/tasks/{task}/attachments', [AttachmentController::class, 'index'])->name('attachments.index');
Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

Route::get('/search', [SearchController::class, 'search'])->name('search');

// Legacy showcase dashboard preserved for diagnostics
Route::get('/showcase', [DashboardController::class, 'index']);
