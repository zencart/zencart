<?php

namespace RyanChandler\BladeCaptureDirective;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BladeCaptureDirectiveServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('blade-capture-directive');
    }

    public function packageBooted()
    {
        Blade::directive('capture', fn (string $expression) => BladeCaptureDirective::open($expression));

        Blade::directive('endcapture', fn () => BladeCaptureDirective::close());
    }
}
