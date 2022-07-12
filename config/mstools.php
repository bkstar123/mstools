<?php
/**
 * @author: tuanha
 * @last-mod: 13-May-2022
*/

return [
    // Maximum file upload, default 5 MB
    'maxFileUpload' => env('MSTOOLS_MAX_FILE_UPLOAD', 5242880),
    'report' => [
        'disk'      => env('MSTOOLS_REPORT_DISK', 'local'),
        'directory' => env('MSTOOLS_REPORT_DIRECTORY', 'reports'),
        'short_ttl' => env('MSTOOLS_REPORT_SHORT_TTL', 5), // How long in minutes to keep short-lived reports on the server
        'long_ttl'  => env('MSTOOLS_REPORT_LONG_TTL', 720) // How long in minutes to keep long-lived reports on the server
    ],
    'tracking' => [
        'dxp'      => env('MSTOOLS_DXP_CNAME_ENDING', '')
    ],
    'pingdom' => [
        'reference_check_id' => env('MSTOOLS_REFERENCE_PINGDOM_CHECK_ID', '')
    ]
];
