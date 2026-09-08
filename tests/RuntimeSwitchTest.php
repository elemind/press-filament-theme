<?php

use Elemind\PressFilamentTheme\Enums\PressVariant;
use Elemind\PressFilamentTheme\PressFilamentTheme;
use Filament\Panel;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Cookie;

function switchPanel(PressFilamentTheme $plugin, ?callable $configure = null): Panel
{
    $panel = Panel::make()->id('runtime')->path('runtime');

    if ($configure) {
        $configure($panel);
    }

    // Panel::boot() registers the render hooks and then boots the plugins, in that
    // order. Booting the plugin by hand would hide anything that depends on it.
    $panel->plugin($plugin);
    $panel->boot();

    return $panel;
}

/**
 * The registered items, raw: getUserMenuItemGroups() injects the profile and
 * logout entries, which resolve through filament() and need a default panel.
 *
 * @return array<int, array<int|string, mixed>>
 */
function registeredUserMenuGroups(Panel $panel): array
{
    $property = new ReflectionProperty($panel, 'userMenuItemGroups');

    return $property->getValue($panel);
}

function withVariantCookie(?string $value): void
{
    request()->cookies->remove(PressFilamentTheme::COOKIE);

    if ($value !== null) {
        request()->cookies->set(PressFilamentTheme::COOKIE, $value);
    }
}

it('is off by default', function () {
    expect(PressFilamentTheme::make()->hasRuntimeSwitch())->toBeFalse();
});

it('leaves the theme static when it is off', function () {
    $panel = switchPanel(PressFilamentTheme::make()->telex());

    expect($panel->getTheme()->getId())->toBe(PressVariant::Telex->getThemeId());
});

it('adds nothing to the user menu when it is off', function () {
    expect(registeredUserMenuGroups(switchPanel(PressFilamentTheme::make())))->toBeEmpty();
});

it('adds a switcher to the user menu when it is on', function () {
    $groups = registeredUserMenuGroups(switchPanel(PressFilamentTheme::make()->runtimeSwitch()));

    expect($groups)->toHaveCount(1)
        ->and($groups[0][0]->getName())->toBe('pressVariant');
});

it('adds nothing to the user menu when the switcher ui is off', function () {
    $panel = switchPanel(PressFilamentTheme::make()->runtimeSwitch()->switcherUi(false));

    expect(registeredUserMenuGroups($panel))->toBeEmpty();
});

it('serves a dynamic theme when it is on', function () {
    $panel = switchPanel(PressFilamentTheme::make()->runtimeSwitch());

    expect($panel->getTheme()->getId())->toBe('press-runtime');
});

it('falls back to the configured variant with no stored choice', function () {
    withVariantCookie(null);

    expect(PressFilamentTheme::make()->gutter()->runtimeSwitch()->resolveVariant())
        ->toBe(PressVariant::Gutter);
});

it('reads the stored choice from the cookie', function () {
    withVariantCookie('vellum');

    expect(PressFilamentTheme::make()->broadsheet()->runtimeSwitch()->resolveVariant())
        ->toBe(PressVariant::Vellum);
});

it('ignores a stored choice that is not a variant', function () {
    withVariantCookie('tabloid');

    expect(PressFilamentTheme::make()->telex()->runtimeSwitch()->resolveVariant())
        ->toBe(PressVariant::Telex);
});

it('ignores the cookie entirely when the switch is off', function () {
    withVariantCookie('vellum');

    expect(PressFilamentTheme::make()->telex()->resolveVariant())->toBe(PressVariant::Telex);
});

it('offers every variant by default', function () {
    expect(PressFilamentTheme::make()->runtimeSwitch()->getAvailableVariants())
        ->toBe(PressVariant::cases());
});

it('offers only the whitelisted variants', function () {
    $plugin = PressFilamentTheme::make()->runtimeSwitch([PressVariant::Telex, PressVariant::Gutter]);

    expect($plugin->getAvailableVariants())->toBe([PressVariant::Telex, PressVariant::Gutter])
        ->and($plugin->hasRuntimeSwitch())->toBeTrue();
});

it('refuses a stored choice outside the whitelist', function () {
    withVariantCookie('vellum');

    $plugin = PressFilamentTheme::make()->telex()->runtimeSwitch([PressVariant::Telex, PressVariant::Gutter]);

    expect($plugin->resolveVariant())->toBe(PressVariant::Telex);
});

it('falls back to the first offered variant when the default is not offered', function () {
    withVariantCookie(null);

    $plugin = PressFilamentTheme::make()->broadsheet()->runtimeSwitch([PressVariant::Gutter, PressVariant::Vellum]);

    expect($plugin->resolveVariant())->toBe(PressVariant::Gutter);
});

it('takes the choice from a resolver instead of the cookie', function () {
    withVariantCookie('vellum');

    $plugin = PressFilamentTheme::make()
        ->runtimeSwitch()
        ->variantResolver(fn (): PressVariant => PressVariant::Gutter);

    expect($plugin->resolveVariant())->toBe(PressVariant::Gutter);
});

it('accepts a plain string from a resolver', function () {
    $plugin = PressFilamentTheme::make()
        ->runtimeSwitch()
        ->variantResolver(fn (): string => 'telex');

    expect($plugin->resolveVariant())->toBe(PressVariant::Telex);
});

it('writes the choice to a cookie', function () {
    PressFilamentTheme::make()->runtimeSwitch()->persistVariant(PressVariant::Vellum);

    $queued = collect(Cookie::getQueuedCookies())
        ->firstWhere(fn ($cookie): bool => $cookie->getName() === PressFilamentTheme::COOKIE);

    expect($queued)->not->toBeNull()
        ->and($queued->getValue())->toBe('vellum');
});

it('hands the choice to a persister instead of the cookie', function () {
    $seen = null;

    PressFilamentTheme::make()
        ->runtimeSwitch()
        ->variantPersister(function (PressVariant $variant) use (&$seen): void {
            $seen = $variant;
        })
        ->persistVariant(PressVariant::Gutter);

    expect($seen)->toBe(PressVariant::Gutter)
        ->and(Cookie::getQueuedCookies())->toBeEmpty();
});

it('registers a standalone preset for every variant', function () {
    switchPanel(PressFilamentTheme::make()->runtimeSwitch());

    foreach (PressVariant::cases() as $variant) {
        $href = FilamentAsset::getStyleHref($variant->getPresetId(), PressFilamentTheme::PACKAGE);

        expect($href)->toContain($variant->getPresetId());
    }
});

it('overrides the preset of a panel that compiles its own theme', function () {
    withVariantCookie('gutter');

    $plugin = PressFilamentTheme::make()->runtimeSwitch();

    $panel = switchPanel(
        $plugin,
        fn (Panel $panel) => $panel->viteTheme('resources/css/filament/admin/theme.css'),
    );

    $rendered = FilamentView::renderHook(PanelsRenderHook::STYLES_AFTER)->toHtml();

    expect($rendered)->toContain(PressVariant::Gutter->getPresetId());
});

it('follows the resolved variant for the fonts', function () {
    withVariantCookie('gutter');

    $panel = switchPanel(PressFilamentTheme::make()->broadsheet()->runtimeSwitch());

    expect($panel->getFontFamily())->toBe(PressVariant::Gutter->getSansFont())
        ->and($panel->getSerifFontFamily())->toBe('ui-serif');
});

it('keeps the serif face of a variant that has one', function () {
    withVariantCookie('broadsheet');

    $panel = switchPanel(PressFilamentTheme::make()->telex()->runtimeSwitch());

    expect($panel->getSerifFontFamily())->toBe(PressVariant::Broadsheet->getSerifFont());
});
