<?php

use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\CaseStudyController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\CxbyExController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\JobController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::permanentRedirect('/home2', '/');

Route::get('/about', AboutController::class)->name('about');
Route::get('/contact', ContactController::class)->name('contact');
Route::get('/cxbyex', CxbyExController::class)->name('cxbyex');
Route::get('/jobs', JobController::class)->name('jobs');
Route::permanentRedirect('/careers', '/jobs');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::permanentRedirect('/work', '/cases');
Route::get('/cases', [CaseStudyController::class, 'index'])->name('cases.index');
Route::redirect('/work/{caseStudy}', '/cases/{caseStudy}', 301);
Route::get('/cases/{caseStudy:slug}', [CaseStudyController::class, 'show'])->name('cases.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
