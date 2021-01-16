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

Route::post('/cms/check-cfzone-ssl', 'GeneralSSLToolController@verifyCFZoneCustomSSL')
  ->name('checkcfzonessl')
  ->middleware('bkscms-auth:admins');

// Verify a certificate's data
Route::get('/cms/check-cert-data', function () {
    return view('cms.checkcertdata');
})->name('checkcertdata')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-cert-data', 'GeneralSSLToolController@verifyCertData')
  ->name('checkcertdata')
  ->middleware('bkscms-auth:admins');

// Upload/update certificate for Cloudlare zones
Route::get('/cms/cfzone-cert-upload', function () {
    return view('cms.cfzonecertupload');
})->name('cfzonecertupload')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/cfzone-cert-upload', 'GeneralSSLToolController@uploadCertCFZone')
  ->name('cfzonecertupload')
  ->middleware('bkscms-auth:admins');