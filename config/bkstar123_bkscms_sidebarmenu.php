<?php
/**
 * Menu array
 * Each link component consists of 'name', 'path', 'icon', 'children' keys
 * 'name', 'path', 'icon' are of string type, 'children' is of array type
 * 'path' for an expandable link should be '#'
 */
return [
    [
        'name' => 'Admin Managment',
        'path' => '#',
        'icon' => 'far fa-user',
        'children' => [
            [
                'name' => 'Admins',
                'path' => '/cms/admins',
                'icon' => 'fa fa-users',
            ],
            [
                'name' => 'Create Admin',
                'path' => '/cms/admins/create',
                'icon' => 'fa fa-user-plus',
            ]
        ]
    ],
    [
        'name' => 'Role Managment',
        'path' => '#',
        'icon' => 'fa fa-certificate',
        'children' => [
            [
                'name' => 'Roles',
                'path' => '/cms/roles',
                'icon' => 'fa fa-user-circle',
            ],
            [
                'name' => 'Create Role',
                'path' => '/cms/roles/create',
                'icon' => 'fa fa-plus',
            ]
        ]
    ],
    [
        'name' => 'Permission Managment',
        'path' => '#',
        'icon' => 'fa fa-universal-access',
        'children' => [
            [
                'name' => 'Permissions',
                'path' => '/cms/permissions',
                'icon' => 'fa fa-ship',
            ],
            [
                'name' => 'Create Permission',
                'path' => '/cms/permissions/create',
                'icon' => 'fa fa-plus',
            ]
        ]
    ],
    [
        'name' => 'SSL Tools',
        'path' => '#',
        'icon' => 'fa fa-lock',
        'children' => [
            [
                'name' => 'Check SSL for domains',
                'path' => '/cms/check-domain-ssl',
                'icon' => 'fa fa-fighter-jet',
            ],
            [
                'name' => 'Check SSL for Cloudflare zones',
                'path' => '/cms/check-cfzone-ssl',
                'icon' => 'fa fa-book',
            ],
            [
                'name' => 'Decode Certificate Data',
                'path' => '/cms/check-cert-data',
                'icon' => 'fa fa-lock',
            ],
            [
                'name' => 'Upload Certificate to Cloudflare',
                'path' => '/cms/cfzone-cert-upload',
                'icon' => 'fa fa-upload',
            ],
            [
                'name' => 'Private Key/Certificate Matching',
                'path' => '/cms/key-cert-matching',
                'icon' => 'fa fa-certificate',
            ],
        ]
    ],
    [
        'name' => 'Cloudflare Firewall Tools',
        'path' => '#',
        'icon' => 'fa fa-lock',
        'children' => [
            [
                'name' => 'Check rule existence for zones',
                'path' => '/cms/verify-firewall-rule-existence',
                'icon' => 'fa fa-camera-retro',
            ],
            [
                'name' => 'Create firewall rule for zones',
                'path' => '/cms/create-firewall-rule',
                'icon' => 'fa fa-plus',
            ],
            [
                'name' => 'Update firewall rule for zones',
                'path' => '/cms/update-firewall-rule',
                'icon' => 'fa fa-terminal',
            ],
            [
                'name' => 'Remove firewall rule for zones',
                'path' => '/cms/delete-firewall-rule',
                'icon' => 'fa fa-trash',
            ],
        ]
    ],
    [
        'name' => 'Cloudflare DNS Tools',
        'path' => '#',
        'icon' => 'fa fa-lock',
        'children' => [
            [
                'name' => 'Get DNS records for hostnames',
                'path' => '/cms/cf-dns-records',
                'icon' => 'fa fa-id-card',
            ],
        ]
    ],
    [
        'name' => 'Go-live tracking DXP sites',
        'path' => '#',
        'icon' => 'fa fa-lock',
        'children' => [
            [
                'name' => 'Trackings',
                'path' => '/cms/trackings',
                'icon' => 'fa fa-list',
            ],
             [
                'name' => 'Create tracking',
                'path' => '/cms/trackings/create',
                'icon' => 'fa fa-plus',
            ],
        ]
    ],
    [
        'name' => 'Miscellaneous Tools',
        'path' => '#',
        'icon' => 'fa fa-desktop',
        'children' => [
            [
                'name' => '.Net Core HTTP Log Json2CSV',
                'path' => '/cms/netcore-http-log-json-to-csv',
                'icon' => 'fa fa-id-card',
            ]
        ]
    ],
    [
        'name' => 'My Recent Files',
        'path' => '/cms/reports/index',
        'icon' => 'fa fa-file',
    ],
    [
        'name' => 'About Me',
        'path' => '/cms/about-me',
        'icon' => 'fa fa-envelope-open',
    ],
    [
        'name' => 'Website',
        'path' => env('APP_WEBSITE'),
        'icon' => 'fa fa-globe',
    ],
];
