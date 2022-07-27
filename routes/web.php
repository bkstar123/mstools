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

Route::get('/cms/about-me', 'AboutController@show')->name('about.show')
  ->middleware('bkscms-auth:admins');

Route::get('/cms/about-me/edit', 'AboutController@edit')->name('about.edit')
  ->middleware('bkscms-auth:admins')
  ->middleware('can:aboutpage.create');

Route::match(['post', 'patch', 'put'], '/cms/about-me/store', 'AboutController@store')
  ->name('about.store')
  ->middleware('bkscms-auth:admins')
  ->middleware('can:aboutpage.create');

Route::any('/ckfinder/connector', '\CKSource\CKFinderBridge\Controller\CKFinderController@requestAction')
  ->name('ckfinder_connector')
  ->middleware('bkscms-auth:admins');

Route::any('/ckfinder/browser', '\CKSource\CKFinderBridge\Controller\CKFinderController@browserAction')
  ->name('ckfinder_browser')
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
        Route::post('pingdom/checks/export', 'PingdomController@exportChecks')
        ->name('pingdom.checks.export');
        Route::post('pingdom/checks', 'PingdomController@getChecks')
        ->name('pingdom.checks');
        Route::post('pingdom/checks/avg-summary', 'PingdomController@getAverageSummary')
        ->name('pingdom.checks.avg.summary');
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

// Check rule existence for zones
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
        ],
    ],
    function () {
        Route::get('verify-firewall-rule-existence', function () {
            return view('cms.verifyruleexistence');
        })->name('verifyruleexistence');
        
        Route::post('verify-firewall-rule-existence', 'CFFirewallController@verifyFWRuleExistence')
        ->name('verifyruleexistence');
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
        
        Route::post('netcore-http-log-json-to-csv', 'JsonToCSVConversionController@handleUploadedHttpLogJsonFile')
        ->name('netcore.httplog.json2csv');
    }
);

// List all files of the current admin
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins'
        ],
    ],
    function () {
        Route::get('reports/index', 'ReportController@index')
        ->name('reports.index');
        Route::get('get-file', 'ReportController@sendFileToBrowser')
        ->name('get-file');
    }
);

// Check DNS records for domains
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
        ],
    ],
    function () {
        Route::post('dns/query', 'DnsController@queryDns')
        ->name('dns.query');
    }
);

// Tracking go-live DXP sites
Route::group(
    [
        'prefix' => 'cms',
        'middleware' => [
            'bkscms-auth:admins',
        ],
    ],
    function () {
        Route::get('trackings', 'TrackingController@index')
        ->name('trackings.index');

        Route::get('trackings/create', 'TrackingController@create')
        ->name('trackings.create')
        ->middleware('can:trackings.create');

        Route::get('trackings/{tracking}', 'TrackingController@show')
        ->name('trackings.show');

        Route::patch('trackings/{tracking}', 'TrackingController@update')
        ->name('trackings.update')
        ->middleware('can:trackings.update,tracking');

        Route::post('trackings', 'TrackingController@store')
        ->name('trackings.store')
        ->middleware('can:trackings.create');

        Route::patch('trackings/{tracking}/on', 'TrackingController@trackingOn')
        ->name('trackings.on')
        ->middleware('can:trackings.on,tracking');

        Route::patch('trackings/{tracking}/off', 'TrackingController@trackingOff')
        ->name('trackings.off')
        ->middleware('can:trackings.off,tracking');

        Route::delete('trackings/{tracking}/destroy', 'TrackingController@destroy')
        ->name('trackings.destroy')
        ->middleware('can:trackings.destroy,tracking');
        Route::delete('trackings', 'TrackingController@massiveDestroy')
        ->name('trackings.massiveDestroy')
        ->middleware('can:trackings.massiveDestroy');
    }
);
