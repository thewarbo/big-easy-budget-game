<?php

// Marketing site
Route::group(['domain' => 'peoplesbudget.{tld}'], function($tld){
    include("www-routes.php");
});

// Auth

Route::get('auth/login', ['as' => 'auth.login', 'uses' => 'Auth\LoginController@getLogin']);
Route::post('auth/login', ['as' => 'auth.login', 'uses' => 'Auth\LoginController@login']);
Route::post('auth/register', ['as' => 'auth.register', 'uses' => 'Auth\LoginController@postRegister']);
Route::get('auth/logout', ['as' => 'auth.logout', 'uses' => 'Auth\LoginController@getLogout']);
Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

// Social Auth
Route::pattern('oauth', 'facebook|linkedin|twitter|google');
Route::get('{oauth}/authorize', 'Auth\LoginController@socialiteAuthorize');
Route::get('{oauth}/login', 'Auth\LoginController@socialiteLogin');

// Frontend
Route::group(['middleware' => ['auth', 'web']], function (){

    Route::get('/budget/list', ['as' => 'game.list', 'uses' => 'GameController@gameList']);
    Route::get('/budget/{budget}', ['as' => 'game.intro', 'uses' => 'GameController@intro']);
    Route::get('/budget/{budget}/play', ['as' => 'game.play', 'uses' => 'GameController@play']);
    Route::post('/budget/{budget}/thanks', ['as' => 'game.save', 'uses' => 'GameController@save', 'middleware' => 'filter.input.result']);
    Route::get('/budget/{budget}/thanks-test', ['as' => 'game.save-test', 'uses' => 'GameController@save']);

    // Ajax
    Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function (){
        Route::get('/organizations/{organization}/details', ['as' => 'ajax.organizations.details', 'uses' => 'OrganizationController@details']);
    });

});

// Admin
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'web', 'role:admin']], function (){

    Route::get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index']);

    // Modules
    Route::get('/budgets/{budget}/open', ['as' => 'budgets.open', 'uses' => 'BudgetController@open']);
    Route::get('/budgets/{budget}/pause', ['as' => 'budgets.pause', 'uses' => 'BudgetController@pause']);
    Route::get('/budgets/{budget}/close', ['as' => 'budgets.close', 'uses' => 'BudgetController@close']);
    Route::get('/budgets/{budget}/export/{type}', ['as' => 'budgets.export', 'uses' => 'BudgetController@download']);

    Route::resource('budgets', 'BudgetController');
    Route::resource('budgets.organizations', 'OrganizationController');
    Route::resource('budgets.categories', 'CategoryController');

});

// Account / City Home
Route::get('/', ['as' => 'home.index', 'uses' => 'HomeController@page']);
Route::get('/{slug}', ['as' => 'home.page', 'uses' => 'HomeController@page']);
