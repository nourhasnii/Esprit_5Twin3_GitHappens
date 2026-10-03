<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\CertificationController;
use App\Http\Controllers\Admin\TraceabilityEventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationRequestController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\QualityCheckController;
use App\Http\Controllers\Admin\OcrCheckController;
use App\Http\Controllers\Admin\TransportConditionController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\VerificationDocumentController;
use App\Http\Controllers\AccountActivationController;
use App\Http\Controllers\AccountVerificationController;
use App\Http\Controllers\Front\ProductController as FrontProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/account/activate/{token}', [AccountActivationController::class, 'show'])->name('account.activation.show');
Route::post('/account/activate', [AccountActivationController::class, 'store'])->name('account.activation.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/account/verification', [AccountVerificationController::class, 'edit'])->name('account.verification.edit');
    Route::post('/account/verification', [AccountVerificationController::class, 'update'])->name('account.verification.update');

    // Back Office (Admin)
    Route::prefix('admin')->name('admin.')->middleware('active.account')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('batches/{batch}/qr', [BatchController::class, 'downloadQr'])->name('batches.qr');
        Route::post('batches/{batch}/regenerate-qr', [BatchController::class, 'regenerateQr'])->name('batches.regenerate-qr');
        Route::resource('batches', BatchController::class);
        Route::get('batches/{batch}/traceability', [TraceabilityEventController::class, 'timeline'])->name('batches.traceability');

        Route::get('certifications/intelligence', [CertificationController::class, 'intelligence'])->name('certifications.intelligence');
        Route::match(['get', 'post'], 'certifications/intelligence/analyze', [CertificationController::class, 'analyze'])->name('certifications.intelligence.analyze.store');
        Route::resource('certifications', CertificationController::class);

        Route::resource('events', TraceabilityEventController::class);
        Route::resource('transport-conditions', TransportConditionController::class);
        Route::patch('alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
        Route::patch('alerts/{alert}/ignore', [AlertController::class, 'ignore'])->name('alerts.ignore');
        Route::patch('alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
        Route::resource('alerts', AlertController::class);
        Route::resource('quality-checks', QualityCheckController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::resource('ocr-checks', OcrCheckController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    });

    Route::prefix('admin')->name('admin.')->middleware('can:manage_users')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::get('verification-requests', [VerificationRequestController::class, 'index'])->name('verification.index');
        Route::get('verification-requests/{verificationRequest}', [VerificationRequestController::class, 'show'])->name('verification.show');
        Route::post('verification-requests/{verificationRequest}/approve', [VerificationRequestController::class, 'approve'])->name('verification.approve');
        Route::post('verification-requests/{verificationRequest}/information', [VerificationRequestController::class, 'requestInformation'])->name('verification.information');
        Route::post('verification-requests/{verificationRequest}/reject', [VerificationRequestController::class, 'reject'])->name('verification.reject');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('verification-documents/{verificationDocument}', [VerificationDocumentController::class, 'download'])->name('verification.documents.download');
    });
});

// Front Office (public)
Route::get('/products', [FrontProductController::class, 'index'])->name('front.products.index');
Route::get('/products/{product}', [FrontProductController::class, 'show'])->name('front.products.show');

require __DIR__.'/auth.php';