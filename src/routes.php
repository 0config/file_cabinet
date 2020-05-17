<?php

use Illuminate\Support\Facades\Route;



Route::group(['namespace' => 'ZeroConfig\App\Http\Controllers', 'middleware' => ['web'] ], function () {

/*    //  file_cabinet route STARTS ------------------------------------
    Route::get('/file_cabinets', 'FileCabinetController@master_list')->name('file_cabinets.master_list');
    Route::get('/file_cabinets/create', 'FileCabinetController@autoIns')->name('file_cabinets.create'); // make sure create comes before detail page
    Route::get('/file_cabinets/detail/{id}', 'FileCabinetController@detail')->name('file_cabinets.detail');
    Route::get('/file_cabinets/edit/{id}', 'FileCabinetController@edit')->name('file_cabinets.edit');
    Route::post('/file_cabinets/edit/{id}', 'FileCabinetController@edit_post'); // for edit POST

    Route::get('/file_cabinets/destroy/{id}', 'FileCabinetController@destroy')->name('file_cabinets.destroy');
//  file_cabinets route ENDS ------------------------------------


    Route::get('/files/{model_name}/{model_id}:{channel}::{id}/', 'UploadFileController@index');
    Route::post('/files/{model_name}/{model_id}:{channel}::{id}/', 'UploadFileController@uploadFile');*/

/*    Route::any('/file_cabinets/x', function () {
        echo " Please see readme.md";
    });*/


});
