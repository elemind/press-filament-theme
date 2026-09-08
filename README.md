# Press — a Filament theme in four editions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elemind/press-filament-theme.svg?style=flat-square)](https://packagist.org/packages/elemind/press-filament-theme)
[![Total Downloads](https://img.shields.io/packagist/dt/elemind/press-filament-theme.svg?style=flat-square)](https://packagist.org/packages/elemind/press-filament-theme)

Press is an editorial theme for Filament v5, built on a shared engine and shipped in four
editions. Each one is a different publication, not a different accent colour: type, shape,
navigation and colour move together.

| Edition | Character |
|---|---|
| **Broadsheet** | Masthead red, warm neutrals, Instrument Serif headings, small caps under a double rule |
| **Telex** | Phosphor green on cold graphite, IBM Plex Mono headings, a bar that stays dark in both modes |
| **Gutter** | Ultramarine on pure neutrals, Archivo, zero radius everywhere, edge-to-edge nav blocks |
| **Vellum** | Iris on violet neutrals, Figtree, generous radii, glass pills and a three-radial wash |

Every edition passes WCAG AA across the panel, in light and dark.

## Installation

```bash
composer require elemind/press-filament-theme
php artisan filament:assets
```

Register the plugin in your panel provider:

```php
use Elemind\PressFilamentTheme\PressFilamentTheme;
use Elemind\PressFilamentTheme\Enums\PressVariant;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            PressFilamentTheme::make()->variant(PressVariant::Telex)
        );
}
```

Each edition also has a shortcut:

```php
PressFilamentTheme::make()->broadsheet();
PressFilamentTheme::make()->telex();
PressFilamentTheme::make()->gutter();
PressFilamentTheme::make()->vellum();
```

With no edition set you get Broadsheet.

## The rail

Press is built around a horizontal navigation. The plugin turns on `topNavigation()` for you
and ships the script that marks which edge of the rail still has content behind it.

```php
PressFilamentTheme::make()->telex()->rail(false); // keep Filament's sidebar
```

The theme degrades to the sidebar on purpose: nothing breaks, the rail styling simply
does not apply.

## Two ways to use it

### Precompiled (default)

The package ships four compiled stylesheets, one per edition. `composer require` and
`php artisan filament:assets` is the whole setup — no npm, no build step in your app.

The trade-off is the one every precompiled Filament theme has: the CSS is built without
seeing your app, so **Tailwind utilities you write in your own Blade files are not
generated**. Filament's own UI is unaffected — it uses semantic `fi-*` classes throughout.

### From source

If you write Tailwind utilities in your Blade files, or you need to compose Press with CSS
from other plugins, compile it yourself. Publish the sources:

```bash
php artisan vendor:publish --tag="press-filament-theme-css"
```

Then import them into your panel's theme file, after Filament's and before your `@source`
directives:

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@import '../../press/engine/engine.css';
@import '../../press/engine/rail.css';
@import '../../press/presets/telex.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
```

You can skip the publish step and import straight out of `vendor/` if you would rather not
duplicate the files — the paths are longer and move with the package:

```css
@import '../../../../vendor/elemind/press-filament-theme/resources/css/engine/engine.css';
```

Keep the plugin registered either way: it still applies the fonts and the rail script. It
detects `viteTheme()` on the panel and stops short of setting its own theme, so the two do
not fight. Be aware that `Panel::getTheme()` gives `viteTheme()` precedence unconditionally
— once your panel compiles its own CSS, that file is the only one Filament will load, and
`applyTheme(true)` cannot change that.

```php
PressFilamentTheme::make()->telex()->applyTheme(false); // never set the packaged theme
```

## Letting people choose

Off by default. Turn it on and everyone picks their own edition:

```php
PressFilamentTheme::make()->broadsheet()->runtimeSwitch();
```

A "Theme" entry appears in the user menu; picking an edition stores the choice and
reloads the page. `->variant()` stays the default for anyone who has not chosen yet.

Offer only some of them:

```php
PressFilamentTheme::make()->runtimeSwitch([PressVariant::Telex, PressVariant::Gutter]);
```

A stored choice outside that list falls back to the default. If the default itself is
not in the list, the first offered edition takes its place — otherwise it would be a
default nobody could return to.

### Where the choice lives

In a cookie, for a year, read on every request — including for guests, so the login
screen keeps the edition someone last used. To keep it somewhere else, hand over the
two ends:

```php
PressFilamentTheme::make()
    ->runtimeSwitch()
    ->variantResolver(fn () => auth()->user()?->press_variant)
    ->variantPersister(fn (PressVariant $variant) => auth()->user()->update([
        'press_variant' => $variant->value,
    ]));
```

The resolver may return a `PressVariant` or its string value. The two are independent:
reading from the database while still writing a cookie is a legitimate combination. Note
that a cookie is per browser, not per identity — the same person on two devices gets two
editions until they choose on each.

### Putting the switcher elsewhere

```php
PressFilamentTheme::make()->runtimeSwitch()->switcherUi(false);   // not in the user menu

PressFilamentTheme::variantAction()                              // put it where you like
```

`->switcherVisible(fn () => auth()->user()->isAdmin())` gates the entry; the closure runs
at render time, so the authenticated user is available.

### With a theme you compile yourself

The switch works in both installation modes, but a panel that compiles its own theme has
to leave the edition out of it. Import the engine and the rail, and **not** a preset:

```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@import '../../press/engine/engine.css';
@import '../../press/engine/rail.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
```

The plugin then adds the chosen preset after your stylesheet. Leaving a preset compiled in
does not work: a preset is written to sit on the engine's defaults, not on another preset,
so any token the compiled one declares and the chosen one does not would survive — you get
one edition's colours with another's typography.

## Fonts

Fonts are served from [Bunny Fonts](https://fonts.bunny.net) (GDPR-friendly, no Google
Fonts request). Each edition sets its own sans, mono and — for Broadsheet — serif face.
Offline installs will want to self-host them.

## Development

```bash
npm install
npm run build          # all four editions
npm run dev:telex      # watch one
composer test
```

## Credits

- [elemind](https://github.com/elemind)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
