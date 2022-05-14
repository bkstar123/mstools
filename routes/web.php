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

// Pingdom routes
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
        ]
    ],
    function () {
        Route::get('export-pingdom-checks', 'PingdomController@exportChecks')
        ->name('exportpingdomchecks');
    }
);

// Verify SSL certificate for domains
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:domains.ssl.check'
        ],
    ],
    function () {
        Route::get('check-domain-ssl', function () {
            return view('cms.checkdomainssl');
        })->name('checkdomainssl');

        Route::post('check-domain-ssl', 'GeneralSSLToolController@verifyDomainSSLData')
        ->name('checkdomainssl');
    }
);

// Verify custom SSL configuration for Cloudflare zones
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:cfzones.customssl.check'
        ],
    ],
    function () {
        Route::get('check-cfzone-ssl', function () {
            return view('cms.checkcfzonessl');
        })->name('checkcfzonessl');

        Route::post('check-cfzone-ssl', 'GeneralSSLToolController@verifyCFZoneCustomSSL')
        ->name('checkcfzonessl');
    }
);

// Verify a certificate's data
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:certificate.decode'
        ],
    ],
    function () {
        Route::get('check-cert-data', function () {
            return view('cms.checkcertdata');
        })->name('checkcertdata');

        Route::post('check-cert-data', 'GeneralSSLToolController@verifyCertData')
        ->name('checkcertdata');
    }
);


// Upload/update certificate for Cloudlare zones
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:cfzone.certificate.update'
        ],
    ],
    function () {
        Route::get('cfzone-cert-upload', function () {
            return view('cms.cfzonecertupload');
        })->name('cfzonecertupload');

        Route::post('cfzone-cert-upload', 'GeneralSSLToolController@uploadCertCFZone')
        ->name('cfzonecertupload');
    }
);

// Key-Cert Matching
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:key.certificate.matching'
        ],
    ],
    function () {
        Route::get('key-cert-matching', function () {
            return view('cms.keycertmatching');
        })->name('keycertmatching');

        Route::post('key-cert-matching', 'GeneralSSLToolController@keyCertMatching')
        ->name('keycertmatching');
    }
);

// Create Cloudflare Firewall Rule
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:cffwrule.create'
        ],
    ],
    function () {
        Route::get('create-firewall-rule', function () {
            return view('cms.createfwrule');
        })->name('createfwrule');
        
        Route::post('create-firewall-rule', 'CFFirewallController@createFWRule')
        ->name('createfwrule');
    }
);

// Update Cloudflare Firewall Rule
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:cffwrule.update'
        ],
    ],
    function () {
        Route::get('update-firewall-rule', function () {
            return view('cms.updatefwrule');
        })->name('updatefwrule');
        
        Route::post('update-firewall-rule', 'CFFirewallController@updateFWRule')
        ->name('updatefwrule');
    }
);

// Delete Cloudflare Firewall Rule
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
            'can:cffwrule.delete'
        ],
    ],
    function () {
        Route::get('delete-firewall-rule', function () {
            return view('cms.deletefwrule');
        })->name('deletefwrule');
        
        Route::post('delete-firewall-rule', 'CFFirewallController@deleteFWRule')
        ->name('deletefwrule');
    }
);

// Convert .NET Core HTTP Log from JSON to CSV
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins'
        ],
    ],
    function () {
        Route::get('netcore-http-log-json-to-csv', function () {
            return view('cms.netcorehttplogjson2csv');
        })->name('netcore.httplog.json2csv');
        
        Route::post('netcore-http-log-json-to-csv', 'MiscellaneousController@handleUploadedHttpLogJsonFile')
        ->name('upload.httplog.jsonfile');
    }
);
