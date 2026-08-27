<?php

use App\Http\Controllers\Api\AppointmentRequestController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\ContactSubmissionController;
use App\Http\Controllers\Api\LegalPageController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SiteSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Astro Frontend API
|--------------------------------------------------------------------------
|
| KDB Hukuk Astro sitesinin (web/) tükettiği herkese açık, salt okunur
| içerik uçları ile iletişim/randevu formu gönderim uçlarıdır.
|
*/

Route::get('site-settings', SiteSettingsController::class);

Route::get('pages/{slug}', [PageController::class, 'show']);
Route::get('legal-pages/{slug}', [LegalPageController::class, 'show']);

Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{slug}', [ServiceController::class, 'show']);

Route::get('blog-categories', [BlogCategoryController::class, 'index']);
Route::get('blog', [BlogPostController::class, 'index']);
Route::get('blog/{slug}', [BlogPostController::class, 'show']);

Route::post('contact', [ContactSubmissionController::class, 'store']);
Route::post('appointments', [AppointmentRequestController::class, 'store']);
