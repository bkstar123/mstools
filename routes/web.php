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

Route::redirect('/', '/cms/admins/login');

Route::get('/cms/dashboard', function () {
    return view('cms.dashboard');
})->name('dashboard.index')
  ->middleware('bkscms-auth:admins');

// Verify SSL certificate for domains
Route::get('/cms/check-domain-ssl', function () {
    return view('cms.checkdomainssl');
})->name('checkdomainssl')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-domain-ssl', 'GeneralSSLToolController@verifyDomainSSLData')
  ->name('checkdomainssl')
  ->middleware('bkscms-auth:admins');

// Verify custom SSL configuration for Cloudflare zones
Route::get('/cms/check-cfzone-ssl', function () {
    return view('cms.checkcfzonessl');
})->name('checkcfzonessl')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-cfzone-ssl', 'GeneralSSLToolController@verifyCustomSSLForCFZones')
  ->name('checkcfzonessl')
  ->middleware('bkscms-auth:admins');
