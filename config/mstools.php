<?php
/**
 * @author: tuanha
 * @last-mod: 13-May-2022
*/

return [
    // Maximum file upload, default 5 MB
    'maxFileUpload' => env('MSTOOLS_MAX_FILE_UPLOAD', 5242880),
    'netcorelog' => [
    	'disk' => env('NETCORE_LOG_DISK', 'local'),
    	'directory' => env('NETCORE_LOG_DIRECTORY', 'dotnetcore-httplog'),
    ],
    'pingdomreport' => [
    	'disk' => env('PINGDOM_REPORT_DISK', 'local'),
    	'directory' => env('PINGDOM_REPORT_DIRECTORY', 'pingdom-report'),
    ],
    'report' => [
        'ttl' => env('MSTOOLS_REPORT_TTL', 5) // How long in minutes to keep reports on the server 
    ]
];
