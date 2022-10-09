<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ config('app.name') }} | @yield('title')
    </title>
    <link href="{{ mix('/cms-assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ mix('/cms-assets/css/plugins.css') }}" rel="stylesheet">
    @stack('css')
    <script src="{{ mix('/cms-assets/js/app.js') }}"></script>
    <script src="{{ mix('/cms-assets/js/plugins.js') }}"></script>
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
    @stack('scriptTop')
    <link rel="apple-touch-icon" sizes="57x57" href="/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>
@guest
    <body class="hold-transition login-page">
        <!-- flashing message -->
        @include('bkstar123_flashing::flashing')
        @yield('content')
        @stack('scriptBottom')
    </body>
@else
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper" id="app">
            <!-- NavBar -->
            @include('cms.layouts.components.navbar')
            <!-- SideBar -->
            @include('cms.layouts.components.sidebar')
            <!-- Contents -->
            @include('cms.layouts.components.contents')
            <!-- Footer -->
            @include('cms.layouts.components.footer')
        </div><!-- ./wrapper -->
        <!-- flashing message -->
        @include('bkstar123_flashing::flashing')
        <script type="text/javascript">
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.upload-certificate-cfzone.completed', (data) => {
                    $.notify(`MSTool has completed the SSL cert uploading request for the given Cloudflare zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.verify-cfzone-customssl.completed', (data) => {
                    $.notify(`MSTool has completed checking custom SSL settings for the given Cloudflare zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.verify-domain-ssldata.completed', (data) => {
                    $.notify(`MSTool has completed checking SSL information for the given domains as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                }); 
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.export-pingdom-check.completed', (data) => {
                    $.notify(`MSTool has completed exporting all Pingdom checks as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                }); 
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.create-cf-fwrule.completed', (data) => {
                    $.notify(`MSTool has completed creating the given Cloudflare firewall rule for multiple zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.update-cf-fwrule.completed', (data) => {
                    $.notify(`MSTool has completed updating the given Cloudflare firewall rule for multiple zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.delete-cf-fwrule.completed', (data) => {
                    $.notify(`MSTool has completed deleting the given Cloudflare firewall rule for multiple zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.verify-existence-cf-fwrule.completed', (data) => {
                    $.notify(`MSTool has completed verifying existence of the given Cloudflare firewall rule for multiple zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.convert-httplog-json-to-csv.completed', (data) => {
                    $.notify(`MSTool has completed converting the JSON log file to CSV as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.check-dns.completed', (data) => {
                    $.notify(`MSTool has completed checking DNS A & CNAME for the given domains as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.get-details-pingdom-checks.completed', (data) => {
                    $.notify(`MSTool has completed getting details of a list of Pingdom checks as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.pingdom-checks.avg.summary.completed', (data) => {
                    $.notify(`MSTool has completed getting average uptime summary of a list of Pingdom checks as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.a.job.failed', (data) => {
                    $.notify('You may get incompleted outcome because MSTool has failed to perform one of necessary jobs while proceeding your requests', {
                        position: "right bottom",
                        className: "error",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
            Echo.private('user-' + {{ auth()->user()->id }})
                .listen('.fetch.cf.dns.hostname.entries.completed', (data) => {
                    $.notify(`MSTool has completed fetching CF DNS hostname entries for the given zones as requested by ${data.requestor}`, {
                        position: "right bottom",
                        className: "success",
                        clickToHide: true,
                        autoHide: false,
                    })
                });
        </script>
        @stack('scriptBottom')
    </body>
@endguest
</html>
