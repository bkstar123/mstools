<?php
/**
 * @author: tuanha
 * @last-mod: 13-May-2022
*/

return [
    // Maximum file upload, default 5 MB
    'maxFileUpload' => env('MSTOOLS_MAX_FILE_UPLOAD', 5242880),
    'report' => [
        'disk' => env('MSTOOLS_REPORT_DISK', 'local'),
        'directory' => env('MSTOOLS_REPORT_DIRECTORY', 'reports'),
        'ttl' => env('MSTOOLS_REPORT_TTL', 5) // How long in minutes to keep reports on the server
    ]
];
