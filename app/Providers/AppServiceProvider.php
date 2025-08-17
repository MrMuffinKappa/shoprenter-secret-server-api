<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Spatie\ArrayToXml\ArrayToXml;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
        { 
            // A response objektumot kiegészítjük az XML válaszhoz szükséges funkcionalitással
            Response::macro('xml', function (array $data, int $status = 200, array $headers = []) {
                $xml = ArrayToXml::convert($data);
                return Response::make($xml, $status, array_merge($headers, ['Content-Type' => 'application/xml']));
            });
        }
}
