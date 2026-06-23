<?php

use App\Livewire\Admin\ApplicantsTable;
use App\Livewire\Admin\ApplicantView;
use App\Livewire\Admin\ArchiveTable;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FinalizationTable;
use App\Livewire\Admin\Login;
use App\Livewire\Public\ApplicationForm;
use App\Livewire\Public\EmailVerification;
use App\Services\AdminActivityLogService;
use App\Services\ApplicantExportService;
use App\Services\ApplicantPassportZipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/verify-email', EmailVerification::class)->name('verify-email');

Route::get('/apply', ApplicationForm::class)->name('apply');

Route::get('/Alogin', Login::class)
    ->middleware('guest:admin')
    ->name('admin.login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', function (AdminActivityLogService $activityLogService) {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $activityLogService->log($admin, 'Admin Logout', 'Administrator signed out of the portal.');
        }

        Auth::guard('admin')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->middleware('auth:admin')->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/applications', ApplicantsTable::class)->name('applications.index');
        Route::get('/applications/{applicant}', ApplicantView::class)->name('applications.show');
        Route::get('/archive', ArchiveTable::class)->name('archive.index');
        Route::get('/finalized', FinalizationTable::class)->name('finalized.index');

        Route::get('/finalized/passport-zip', function (Request $request, ApplicantPassportZipService $passportZipService, AdminActivityLogService $activityLogService) {
            $ids = $request->query('ids', []);

            if (! is_array($ids)) {
                $ids = explode(',', (string) $ids);
            }

            $ids = array_values(array_filter(array_map('intval', $ids)));

            if ($ids === []) {
                abort(400, 'Select at least one applicant.');
            }

            $activityLogService->log(
                Auth::guard('admin')->user(),
                'Passport Zip Downloaded',
                'Downloaded passport photos for '.count($ids).' selected finalized applicant(s).',
            );

            return $passportZipService->download($ids);
        })->name('finalized.passport-zip');

        Route::get('/export', function (Request $request, ApplicantExportService $exportService, AdminActivityLogService $activityLogService) {
            $activityLogService->log(
                Auth::guard('admin')->user(),
                'Export Generated',
                'Exported application records to Excel.',
            );

            return $exportService->download([
                'scope' => $request->query('scope'),
                'search' => $request->query('q'),
                'barangay' => $request->query('barangay'),
                'date_from' => $request->query('from'),
                'date_to' => $request->query('to'),
            ]);
        })->name('export');
    });
});
