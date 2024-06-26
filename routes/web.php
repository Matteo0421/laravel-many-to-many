<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Guest\PageController;
use App\Http\Controllers\Admin\DashBoardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TecnologyController;
use App\Http\Controllers\Admin\TypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PageController::class, 'index'])->name('home');


Route::middleware(['auth', 'verified'])
                   ->prefix('admin')
                   ->name('admin.')
                   ->group(function(){
                    // qui vengono messe tutte le rotte protette da auth
                    Route::get('/', [DashBoardController::class, 'index'])->name('home');
                    Route::resource('projects', ProjectController::class);
                    Route::resource('tecnologies', TecnologyController::class);
                    Route::resource('types', TypeController::class);

                    Route::get('type-ptojects', [TypeController::class, 'typeProjects'])->name('type_projects');


                   });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
