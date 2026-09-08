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
