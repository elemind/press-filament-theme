<?php

namespace Elemind\PressFilamentTheme;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PressFilamentThemeServiceProvider extends PackageServiceProvider
{
    public static string $name = 'press-filament-theme';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }
}
