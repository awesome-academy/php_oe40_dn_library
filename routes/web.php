<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\AuthorController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PublisherController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\BorrowRequestController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\LibraryController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::name('library.')->group(function () {

    Route::get('/books', [LibraryController::class, 'index'])->name('index');

    Route::get('/book/{book}', [LibraryController::class, 'viewBook'])->name('book');

    Route::post('/book/{book}/borrow', [LibraryController::class, 'borrowBook'])->name('borrow');

    Route::get('/borrows', [LibraryController::class, 'borrowHistory'])->name('borrow.history');

    Route::post('/book/{book}/like', [LibraryController::class, 'toggleLike'])->name('like');

    Route::post('/{followableType}/{followableId}/follow', [LibraryController::class, 'toggleFollow'])->name('follow');

    Route::post('/book/{book}/rate', [LibraryController::class, 'rateBook'])->name('rate');

    Route::get('/author/{author}', [LibraryController::class, 'viewAuthor'])->name('author');

});

Route::prefix('dashboard')->middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::resource('permissions', PermissionController::class)->only([
        'index',
        'edit',
        'update',
    ]);

    // TODO: Redirect users.show to ProfileController
    Route::resource('users', UserController::class)->except([
        'show',
    ]);

    Route::resource('categories', CategoryController::class)->except([
        'show',
    ]);

    // TODO: Redirect authors.show to
    Route::resource('authors', AuthorController::class)->except([
        'show',
    ]);

    Route::resource('publishers', PublisherController::class)->except([
        'show',
    ]);

    Route::get('/borrows', [BorrowRequestController::class, 'index'])->name('borrows.index');

    Route::post('/borrows/{borrowRequest}/{action}', [BorrowRequestController::class, 'update'])->name('borrows.update');

});

Route::get('/locale/{locale?}', function ($locale = 'en') {
    App::setLocale($locale);
    Session::put('locale', $locale);

    return back();
});

Auth::routes([
    'reset' => false,
]);
