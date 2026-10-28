<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ArticleCrudController;
use App\Http\Controllers\Admin\AttributeCrudController;
use App\Http\Controllers\Admin\AttributeGroupCrudController;
use App\Http\Controllers\Admin\AttributeSetCrudController;
use App\Http\Controllers\Admin\CategoryCrudController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqCrudController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\InboxController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NavCrudController;
use App\Http\Controllers\Admin\PageCrudController;
use App\Http\Controllers\Admin\ProductCrudController;
use App\Http\Controllers\Admin\ProductFaqCrudController;
use App\Http\Controllers\Admin\RetailerCrudController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // ---- authentication ----
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {

        Route::get('/', DashboardController::class)->name('dashboard');

        // ---- launch checklist & own account ----
        Route::get('checklist', [ChecklistController::class, 'index'])->name('checklist');
        Route::post('checklist/{step}', [ChecklistController::class, 'toggle'])->name('checklist.toggle');
        Route::get('account', [AccountController::class, 'edit'])->name('account');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');

        // ---- schema-driven resources ----
        $resources = [
            'products'         => ProductCrudController::class,
            'categories'       => CategoryCrudController::class,
            'attributes'       => AttributeCrudController::class,
            'attribute-groups' => AttributeGroupCrudController::class,
            'attribute-sets'   => AttributeSetCrudController::class,
            'product-faqs' => ProductFaqCrudController::class,
            'articles'     => ArticleCrudController::class,
            'pages'        => PageCrudController::class,
            'faqs'         => FaqCrudController::class,
            'retailers'    => RetailerCrudController::class,
            'nav'          => NavCrudController::class,
        ];

        foreach ($resources as $slug => $controller) {
            Route::prefix($slug)->name($slug.'.')->group(function () use ($controller) {
                Route::get('/', [$controller, 'index'])->name('index');
                Route::get('create', [$controller, 'create'])->name('create');
                Route::post('/', [$controller, 'store'])->name('store');
                Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
                Route::put('{id}', [$controller, 'update'])->name('update');
                Route::delete('{id}', [$controller, 'destroy'])->name('destroy');
            });
        }

        // ---- home page composer ----
        Route::prefix('sections')->name('sections.')->group(function () {
            Route::get('/', [SectionController::class, 'index'])->name('index');
            Route::get('create', [SectionController::class, 'create'])->name('create');
            Route::post('/', [SectionController::class, 'store'])->name('store');
            Route::get('{section}/edit', [SectionController::class, 'edit'])->name('edit');
            Route::put('{section}', [SectionController::class, 'update'])->name('update');
            Route::delete('{section}', [SectionController::class, 'destroy'])->name('destroy');
            Route::post('{section}/move', [SectionController::class, 'move'])->name('move');
            Route::post('{section}/toggle', [SectionController::class, 'toggle'])->name('toggle');
        });

        // ---- settings & product import ----
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('import', [ImportController::class, 'index'])->name('import.index');
        Route::post('import/folder', [ImportController::class, 'folder'])->name('import.folder');
        Route::post('import/upload', [ImportController::class, 'upload'])->name('import.upload');

        // ---- media library ----
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        // ---- reviews ----
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/toggle', [ReviewController::class, 'toggle'])->name('reviews.toggle');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // ---- inbox ----
        Route::get('subscribers', [InboxController::class, 'subscribers'])->name('subscribers');
        Route::get('subscribers/export', [InboxController::class, 'exportSubscribers'])->name('subscribers.export');
        Route::delete('subscribers/{subscriber}', [InboxController::class, 'destroySubscriber'])->name('subscribers.destroy');

        Route::get('messages', [InboxController::class, 'messages'])->name('messages');
        Route::delete('messages/{message}', [InboxController::class, 'destroyMessage'])->name('messages.destroy');
    });
});
