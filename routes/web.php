
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Main Admin Dashboard: /
    Route::get('/', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    // Alternative dashboard URL: /admin
    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    })->name('admin.home');


    // Manage Users and Verification
    Route::get('/admin/users', [AdminController::class, 'verifications'])
        ->name('admin.users');

    Route::get('/admin/verifications', [AdminController::class, 'verifications'])
        ->name('admin.verifications');

    Route::post('/admin/verifications/{id}', [AdminController::class, 'updateStatus'])
        ->name('admin.update');


    // Pending Livestock Listings
    Route::get('/admin/pending-listings', [AdminController::class, 'pendingListings'])
        ->name('admin.pending.listings');

    Route::post('/admin/pending-listings/{id}', [AdminController::class, 'updateListingStatus'])
        ->name('admin.pending.update');


    // Livestock Listings
    Route::get('/admin/listings', [AdminController::class, 'listings'])
        ->name('admin.listings');

    Route::delete('/admin/listings/{id}', [AdminController::class, 'deleteListing'])
        ->name('admin.deleteListing');


    // Transactions
    Route::get('/admin/transactions', [AdminController::class, 'transactions'])
        ->name('admin.transactions');


    // Deliveries
    Route::get('/admin/deliveries', [AdminController::class, 'deliveries'])
        ->name('admin.deliveries');


    // Payment Monitoring
    Route::get('/admin/payments', [AdminController::class, 'payments'])
        ->name('admin.payments');


    // Activity Logs
    Route::get('/admin/logs', [AdminController::class, 'activityLogs'])
        ->name('admin.logs');


    // Settings
    Route::get('/admin/settings', [AdminController::class, 'settings'])
        ->name('admin.settings');

    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])
        ->name('admin.settings.update');


    // Support Tickets
    Route::get('/admin/support', [AdminController::class, 'supportTickets'])
        ->name('admin.support');

    Route::post('/admin/support/{id}/resolve', [AdminController::class, 'resolveSupportTicket'])
        ->name('admin.support.resolve');
    
    Route::get('/verify-registration', [AuthController::class, 'verifyRegistration'])
        ->name('verify.registration');

});
