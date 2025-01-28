<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\HMRCGatewayInterface;
use App\Repositories\HMRCGatewayRepository;
use App\Repositories\Interfaces\HMRCGatewayMessageInterface;
use App\Repositories\HMRCGatewayMessageRepository;
use App\Repositories\Interfaces\HMRC_RTI_Interface;
use App\Repositories\HMRC_RTI_Repository;
use App\Repositories\Interfaces\HMRC_RTI_FPS_Interface;
use App\Repositories\HMRC_RTI_FPS_Repository;
use App\Repositories\Interfaces\HMRC_RTI_EPS_Interface;
use App\Repositories\HMRC_RTI_EPS_Repository;
use App\Repositories\Interfaces\HMRC_RTI_EAS_Interface;
use App\Repositories\HMRC_RTI_EAS_Repository;
use App\Repositories\Interfaces\HMRC_VAT_Interface;
use App\Repositories\HMRC_VAT_Repository;
use App\Repositories\Interfaces\Payroll_Interface;
use App\Repositories\Payroll_Repository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(HMRCGatewayInterface::class, HMRCGatewayRepository::class);
        $this->app->bind(HMRCGatewayMessageInterface::class, HMRCGatewayMessageRepository::class);
        $this->app->bind(HMRC_RTI_Interface::class, HMRC_RTI_Repository::class);
        $this->app->bind(HMRC_RTI_FPS_Interface::class, HMRC_RTI_FPS_Repository::class);
        $this->app->bind(HMRC_RTI_EPS_Interface::class, HMRC_RTI_EPS_Repository::class);
        $this->app->bind(HMRC_RTI_EAS_Interface::class, HMRC_RTI_EAS_Repository::class);
        $this->app->bind(HMRC_VAT_Interface::class, HMRC_VAT_Repository::class);
        $this->app->bind(Payroll_Interface::class, Payroll_Repository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
