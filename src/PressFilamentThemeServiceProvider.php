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

    public function packageBooted(): void
    {
        // Only the engine and the presets: the entry files next to them exist to build
        // this package's own stylesheets and resolve against its vendor directory.
        $this->publishes([
            __DIR__ . '/../resources/css/engine' => resource_path('css/press/engine'),
            __DIR__ . '/../resources/css/presets' => resource_path('css/press/presets'),
        ], 'press-filament-theme-css');
    }
}
