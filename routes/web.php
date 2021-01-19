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
    if (Gate::allows('domains.ssl.check')) {
        return view('cms.checkdomainssl');
    } else {
        abort('403');
    }
})->name('checkdomainssl')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-domain-ssl', 'GeneralSSLToolController@verifyDomainSSLData')
  ->name('checkdomainssl')
  ->middleware('bkscms-auth:admins');

// Verify custom SSL configuration for Cloudflare zones
Route::get('/cms/check-cfzone-ssl', function () {
    if (Gate::allows('cfzones.customssl.check')) {
        return view('cms.checkcfzonessl');
    } else {
        abort('403');
    }
})->name('checkcfzonessl')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-cfzone-ssl', 'GeneralSSLToolController@verifyCFZoneCustomSSL')
  ->name('checkcfzonessl')
  ->middleware('bkscms-auth:admins');

// Verify a certificate's data
Route::get('/cms/check-cert-data', function () {
    if (Gate::allows('certificate.decode')) {
        return view('cms.checkcertdata');
    } else {
        abort('403');
    }
})->name('checkcertdata')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/check-cert-data', 'GeneralSSLToolController@verifyCertData')
  ->name('checkcertdata')
  ->middleware('bkscms-auth:admins');

// Upload/update certificate for Cloudlare zones
Route::get('/cms/cfzone-cert-upload', function () {
    if (Gate::allows('cfzone.certificate.update')) {
        return view('cms.cfzonecertupload');
    } else {
        abort('403');
    }
})->name('cfzonecertupload')
  ->middleware('bkscms-auth:admins');

Route::post('/cms/cfzone-cert-upload', 'GeneralSSLToolController@uploadCertCFZone')
  ->name('cfzonecertupload')
  ->middleware('bkscms-auth:admins');