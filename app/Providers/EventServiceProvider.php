<?php

namespace App\Providers;

use App\Events\CheckDNSCompleted;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Events\CreateCFFWRuleCompleted;
use App\Events\DeleteCFFWRuleCompleted;
use App\Events\UpdateCFFWRuleCompleted;
use App\Events\ExportPingdomChecksCompleted;
use App\Events\VerifyDomainSSLDataCompleted;
use App\Events\HttpLogJson2CsvConversionDone;
use App\Events\VerifyCFZoneCustomSSLCompleted;
use App\Events\ExportCF4SaaSHostnamesCompleted;
use App\Events\GetPingdomChecksDetailsCompleted;
use App\Events\VerifyExistenceCFFWRuleCompleted;
use App\Events\GetPingdomChecksAvgSummaryCompleted;
use App\Listeners\SendNotificationCheckDNSCompleted;
use App\Events\FetchCFDNSTargetsForHostnamesCompleted;
use App\Events\FetchDNSHostnameRecordsForZonesCompleted;
use App\Listeners\SendNotificationChecksAvgSumCompleted;
use App\Listeners\SendNotificationCreateCFFWRuleCompleted;
use App\Listeners\SendNotificationDeleteCFFWRuleCompleted;
use App\Listeners\SendNotificationDomainSSLDataCompletion;
use App\Listeners\SendNotificationUpdateCFFWRuleCompleted;
use App\Listeners\SendNotificationCFZoneSSLUploadCompleted;
use App\Events\UploadCustomCertificateToCloudflareCompleted;
use App\Listeners\SendNotificationCFZoneCustomSSLCompletion;
use App\Listeners\SendNotificationPingdomCheckExportCompletion;
use App\Listeners\SendNotificationHttpLogJson2CsvConversionDone;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Listeners\SendNotificationExportCF4SaaSHostnamesCompleted;
use App\Listeners\SendNotificationGetPingdomChecksDetailsCompleted;
use App\Listeners\SendNotificationVerifyExistenceCFFWRuleCompleted;
use App\Listeners\SendNotificationFetchCFDNSTargetsForHostnamesCompleted;
use App\Listeners\SendNotificationFetchDNSHostnameRecordsForZonesCompleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        VerifyCFZoneCustomSSLCompleted::class => [
            SendNotificationCFZoneCustomSSLCompletion::class
        ],
        VerifyDomainSSLDataCompleted::class => [
            SendNotificationDomainSSLDataCompletion::class
        ],
        UploadCustomCertificateToCloudflareCompleted::class => [
            SendNotificationCFZoneSSLUploadCompleted::class
        ],
        ExportPingdomChecksCompleted::class => [
            SendNotificationPingdomCheckExportCompletion::class
        ],
        CreateCFFWRuleCompleted::class => [
            SendNotificationCreateCFFWRuleCompleted::class
        ],
        UpdateCFFWRuleCompleted::class => [
            SendNotificationUpdateCFFWRuleCompleted::class
        ],
        DeleteCFFWRuleCompleted::class => [
            SendNotificationDeleteCFFWRuleCompleted::class
        ],
        VerifyExistenceCFFWRuleCompleted::class => [
            SendNotificationVerifyExistenceCFFWRuleCompleted::class
        ],
        HttpLogJson2CsvConversionDone::class => [
            SendNotificationHttpLogJson2CsvConversionDone::class
        ],
        CheckDNSCompleted::class => [
            SendNotificationCheckDNSCompleted::class
        ],
        GetPingdomChecksDetailsCompleted::class => [
            SendNotificationGetPingdomChecksDetailsCompleted::class
        ],
        GetPingdomChecksAvgSummaryCompleted::class => [
            SendNotificationChecksAvgSumCompleted::class
        ],
        FetchDNSHostnameRecordsForZonesCompleted::class => [
            SendNotificationFetchDNSHostnameRecordsForZonesCompleted::class
        ],
        FetchCFDNSTargetsForHostnamesCompleted::class => [
            SendNotificationFetchCFDNSTargetsForHostnamesCompleted::class
        ],
        ExportCF4SaaSHostnamesCompleted::class => [
            SendNotificationExportCF4SaaSHostnamesCompleted::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
