<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupsController;

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

Route::get('/', [GroupsController::class, 'AllGroups']);

Route::get('/all', [GroupsController::class, 'AllGroups']);
Route::get('/all/{order}', function($order){
    $temp = new GroupsController();
    return $temp->AllGroups($order);
});
