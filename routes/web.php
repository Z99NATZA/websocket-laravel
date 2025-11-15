<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\Chat\Index::class);
Route::post('/chat', [App\Http\Controllers\Chat\ChatController::class, 'chat'])->name('chat.post');
Route::post('/clear', [App\Http\Controllers\Chat\ChatController::class, 'clear'])->name('chat.clear');
