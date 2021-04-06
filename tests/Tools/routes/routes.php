<?php

use Illuminate\Support\Facades\Route;

// use VGirol\JsonApi\Tests\Tools\Controller\CommentController;
// use VGirol\JsonApi\Tests\Tools\Controller\PhotoController;
// use VGirol\JsonApi\Tests\Tools\Controller\PriceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::jsonApiResource(
    'photos',
    null,
    ['relationships' => true]
);
Route::jsonApiResource(
    'prices'
);
Route::jsonApiResource(
    'comments',
    null,
    ['relationships' => true]
);
Route::jsonApiResource(
    'authors',
    null,
    ['relationships' => true]
);
Route::jsonApiResource(
    'tags'
);
