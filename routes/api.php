<?php

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return [
        'app' => config('app.name'),
        'version' => '1.0.0',
    ];
});

// DELIVERY
Route::get('city', 'Ecommerce\CartController@getCity');
Route::get('district', 'Ecommerce\CartController@getDistrict');
Route::post('cost', 'Ecommerce\CartController@getCourier');

Route::group(['prefix' => 'product'], function() {
    // WEB PAGE PUBLIC (PRODUCT)
    Route::get('/', 'Ecommerce\FrontController@index')->name('front.index');
    Route::get('/all', 'Ecommerce\FrontController@product')->name('front.product');
    Route::get('/category/{slug}', 'Ecommerce\FrontController@categoryProduct')->name('front.category');
    Route::get('/products/{slug}', 'Ecommerce\FrontController@show')->name('front.show_product');    
    Route::get('/categories', 'Ecommerce\FrontController@showall')->name('category.all');
});

Route::group(['middleware' => 'auth:api'], function () {
    // ADMIN PAGE
    // account profile
    Route::get('auth/me', function (Request $request) {
        return new UserResource($request->user());
    });

    // category
    Route::resource('category', 'CategoryController')->except(['create', 'show']);

    // product
    Route::resource('product', 'ProductController')->except(['show']);

    // product marketplace
    Route::post('/product/marketplace', 'ProductController@uploadViaMarketplace')->name('product.marketplace');

    // order at admin page
    Route::group(['prefix' => 'orders'], function() {
        Route::get('/', 'OrderController@index')->name('orders.index');
        Route::get('/{invoice}', 'OrderController@view')->name('orders.view');
        Route::get('/payment/{invoice}', 'OrderController@acceptPayment')->name('orders.approve_payment');
        Route::post('/shipping', 'OrderController@shippingOrder')->name('orders.shipping');
        Route::delete('/{id}', 'OrderController@destroy')->name('orders.destroy');
        Route::get('/return/{invoice}', 'OrderController@return')->name('orders.return');
        Route::post('/return', 'OrderController@approveReturn')->name('orders.approve_return');
    });

    // order at member page
    Route::group(['prefix' => 'member',  'namespace' => 'Ecommerce'], function() {        
        Route::get('orders', 'OrderController@index')->name('customer.orders');
        Route::get('orders/{invoice}', 'OrderController@view')->name('customer.view_order');
        Route::get('orders/pdf/{invoice}', 'OrderController@pdf')->name('customer.order_pdf');
        Route::post('orders/accept', 'OrderController@acceptOrder')->name('customer.order_accept');
        Route::get('orders/return/{invoice}', 'OrderController@returnForm')->name('customer.order_return');
        Route::put('orders/return/{invoice}', 'OrderController@processReturn')->name('customer.return');
    });
});

Route::group(['middleware' => 'guest:api'], function () {
    // REQUEST LOGIN OR REGISTER
    // login as admin or customer
    Route::post('auth/login', 'Auth\LoginController@login');
    Route::post('auth/register', 'Auth\RegisterController@register');

    // email reset
    Route::get('password/email', 'Auth\ForgotPasswordController@showLinkRequestForm');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});