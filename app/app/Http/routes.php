<?php

// Marketing site
Route::group(['domain' => 'peoplesbudget.{tld}'], function($tld){
    include("www-routes.php");
});

// Auth


// Social Auth
Route::pattern('oauth', 'facebook|linkedin|twitter|google');
Route::get('{oauth}/authorize', 'Auth\LoginController@socialiteAuthorize');
Route::get('{oauth}/login', 'Auth\LoginController@socialiteLogin');

// Frontend
Route::group(['middleware' => ['auth']], function (){

    Route::get('/budget/list', ['as' => 'game.list', 'uses' => 'GameController@gameList']);
    Route::get('/budget/{budgets}', ['as' => 'game.intro', 'uses' => 'GameController@intro']);
    Route::get('/budget/{budgets}/play', ['as' => 'game.play', 'uses' => 'GameController@play']);
    Route::post('/budget/{budgets}/thanks', ['as' => 'game.save', 'uses' => 'GameController@save', 'middleware' => 'filter.input.result']);
    Route::get('/budget/{budgets}/thanks-test', ['as' => 'game.save-test', 'uses' => 'GameController@save']);

    // Ajax
    Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function (){
        Route::get('/organizations/{organizations}/details', ['as' => 'ajax.organizations.details', 'uses' => 'OrganizationController@details']);
    });

});

// Admin
Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'role:admin']], function (){

    Route::get('/', ['as' => 'admin.dashboard', 'uses' => 'DashboardController@index']);

    // Modules
    Route::get('/budgets/{budgets}/open', ['as' => 'admin.budgets.open', 'uses' => 'BudgetController@open']);
    Route::get('/budgets/{budgets}/pause', ['as' => 'admin.budgets.pause', 'uses' => 'BudgetController@pause']);
    Route::get('/budgets/{budgets}/close', ['as' => 'admin.budgets.close', 'uses' => 'BudgetController@close']);
    Route::get('/budgets/{budgets}/export/{type}', ['as' => 'admin.budgets.export', 'uses' => 'BudgetController@download']);

    Route::resource('budgets', 'BudgetController');
    Route::resource('budgets.organizations', 'OrganizationController');
    Route::resource('budgets.categories', 'CategoryController');

});

// Account / City Home
Route::get('/', ['as' => 'home.index', 'uses' => 'HomeController@page']);
Route::get('/{slug}', ['as' => 'home.page', 'uses' => 'HomeController@page']);
