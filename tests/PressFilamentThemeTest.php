<?php

use Elemind\PressFilamentTheme\Enums\PressVariant;
use Elemind\PressFilamentTheme\PressFilamentTheme;
use Filament\Panel;
use Filament\Support\Facades\FilamentAsset;

function pressPanel(PressFilamentTheme $plugin, ?callable $configure = null): Panel
{
    $panel = Panel::make()->id('testing')->path('testing');

    if ($configure) {
        $configure($panel);
    }

    $panel->plugin($plugin);
    $panel->boot();

    return $panel;
}

it('defaults to broadsheet', function () {
    expect(PressFilamentTheme::make()->getVariant())->toBe(PressVariant::Broadsheet);
});

it('applies the theme of the selected variant', function (PressVariant $variant) {
    $panel = pressPanel(PressFilamentTheme::make()->variant($variant));

    expect($panel->getTheme()->getId())->toBe($variant->getThemeId());
})->with(PressVariant::cases());

it('applies the fonts of the selected variant', function (PressVariant $variant) {
    $panel = pressPanel(PressFilamentTheme::make()->variant($variant));

    expect($panel->getFontFamily())->toBe($variant->getSansFont())
        ->and($panel->getMonoFontFamily())->toBe($variant->getMonoFont());

    if (filled($serifFont = $variant->getSerifFont())) {
        expect($panel->getSerifFontFamily())->toBe($serifFont)
            ->and($panel->getSerifFontUrl())->toBe($variant->getSerifFontUrl());
    }
})->with(PressVariant::cases());

it('has a shortcut for each variant', function (PressVariant $variant) {
    $plugin = PressFilamentTheme::make()->{$variant->value}();

    expect($plugin->getVariant())->toBe($variant);
})->with(PressVariant::cases());

it('registers a compiled theme for every variant', function () {
    pressPanel(PressFilamentTheme::make());

    foreach (PressVariant::cases() as $variant) {
        $theme = FilamentAsset::getTheme($variant->getThemeId());

        expect($theme)->not->toBeNull()
            ->and($theme->getPath())->toBeFile();
    }
});

it('registers the rail script', function () {
    pressPanel(PressFilamentTheme::make());

    $scripts = FilamentAsset::getScripts([PressFilamentTheme::PACKAGE]);

    expect($scripts)->toHaveCount(1)
        ->and(reset($scripts)->getId())->toBe('press-rail');
});

it('enables top navigation by default', function () {
    expect(pressPanel(PressFilamentTheme::make())->hasTopNavigation())->toBeTrue();
});

it('leaves navigation alone when the rail is off', function () {
    $panel = pressPanel(PressFilamentTheme::make()->rail(false));

    expect($panel->hasTopNavigation())->toBeFalse();
});

it('does not override a panel that compiles its own theme with vite', function () {
    $panel = pressPanel(
        PressFilamentTheme::make()->telex(),
        fn (Panel $panel) => $panel->viteTheme('resources/css/filament/admin/theme.css'),
    );

    expect($panel->getViteTheme())->toBe('resources/css/filament/admin/theme.css');
});

it('cannot override a vite theme even when applyTheme is forced on', function () {
    // Panel::getTheme() resolves viteTheme() unconditionally and overwrites whatever
    // theme() was given, so a panel compiling its own CSS must import the Press
    // sources into it. The flag exists to be explicit, not to win that fight.
    $plugin = PressFilamentTheme::make()->telex()->applyTheme();

    $panel = Panel::make()->id('testing')->path('testing')
        ->viteTheme('resources/css/filament/admin/theme.css')
        ->plugin($plugin);

    $panel->boot();

    expect($panel->getViteTheme())->toBe('resources/css/filament/admin/theme.css');
});

it('skips the theme when applyTheme is forced off', function () {
    $panel = pressPanel(PressFilamentTheme::make()->telex()->applyTheme(false));

    expect($panel->getTheme()->getId())->not->toBe(PressVariant::Telex->getThemeId());
});
