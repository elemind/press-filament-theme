<?php

namespace Elemind\PressFilamentTheme;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;

class PressFilamentTheme implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'press-filament-theme';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register(
            assets: [
                Theme::make('press-filament-theme', __DIR__ . '/../resources/dist/press-filament-theme.css'),
            ],
            package: 'elemind/press-filament-theme',
        );

        $panel
            ->font('DM Sans')
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->theme('press-filament-theme');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
