<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\GoogleDrive\Http\Controllers\GoogleDriveController;
use Modules\GoogleDrive\Entities\GoogleDriveSetting;
use Illuminate\Http\Request;

Route::group(['middleware' => ['web','auth','verified','PlanModuleCheck:GoogleDrive']], function ()
{
    Route::get('/auth/google', function(Request $request)
    {
        $status =  GoogleDriveSetting::redirectToGoogle($request);

        if(!$status){
            echo '<script>
                    window.close();
                    // Access the main window using window.opener
                    var mainWindow = window.opener;
                    // Show Toastr success message
                    mainWindow.toastrs("Error", "Something Went Wrong.", "error");
                    mainWindow.location.reload();
                </script>';
        }else{
            return $status;

        }

    })->name('auth.google');

    Route::get('/google/callback', function(Request $request)
    {
        GoogleDriveSetting::handleGoogleCallback($request->code);

        echo '<script>
                    window.close();
                    // Access the main window using window.opener
                    var mainWindow = window.opener;
                    // Show Toastr success message
                    mainWindow.toastrs("Success", "Google Drive Authenticated Successfully.", "success");
                    mainWindow.location.reload();
                </script>';
    })->name('auth.google.callback');

    Route::any('googledrive/folder/assigned/{module?}', [GoogleDriveController::class, 'assign_folder_store'])
        ->name('assigned.folder');

    Route::any('googledrive/folder/assign/{module?}', [GoogleDriveController::class, 'assign_folder'])
        ->name('assign.folder');

    Route::any('googledrive/folder/create/{module}/{parentid?}', [GoogleDriveController::class, 'create_folder'])
        ->name('create.new.folder');

    Route::any('googledrive/folder/store/{module}/{parentid?}', [GoogleDriveController::class, 'store_folder'])
        ->name('store.new.folder');

    Route::any('googledrive/uploadfiles/{modulename}', [GoogleDriveController::class, 'uploadfiles_store'])
        ->name('upload.file.store');

    Route::get('googledrive/{module}', [GoogleDriveController::class, 'uploadfiles_create'])
        ->name('upload.file.create');

    Route::get('googledrive/delete/{folderid}', [GoogleDriveController::class, 'delete_file'])
        ->name('file.delete');

    Route::get('googledrives/{module}/{folderid?}/{view?}', [GoogleDriveController::class, 'index'])
        ->name('googledrive.index');

    Route::get('googledrive/{module}/{folderid?}/{view?}', [GoogleDriveController::class, 'getmodulefiles'])
        ->name('googledrive.module.index');

    Route::post('google-drive-settings-save', [GoogleDriveController::class, 'GoogleDriveSettingsStore'])
        ->name('google.drive.setting.store');

});
