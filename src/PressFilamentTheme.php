<?php

namespace Elemind\PressFilamentTheme;

use Closure;
use Elemind\PressFilamentTheme\Actions\SwitchVariantAction;
use Elemind\PressFilamentTheme\Concerns\ResolvesVariant;
use Elemind\PressFilamentTheme\Enums\PressVariant;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\FontProviders\BunnyFontProvider;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class PressFilamentTheme implements Plugin
{
    use ResolvesVariant;

    /**
     * @internal
     */
    public const PACKAGE = 'elemind/press-filament-theme';

    protected PressVariant $variant = PressVariant::Broadsheet;

    protected bool $rail = true;

    /**
     * null means "decide at boot": the theme is applied unless the panel already
     * compiles its own with viteTheme(), in which case the app is expected to
     * import the Press sources into that file instead.
     */
    protected ?bool $applyTheme = null;

    protected bool $switcherUi = true;

    protected bool | Closure $switcherVisible = true;

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @internal
     */
    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * The switcher, for anywhere other than the user menu: a settings page, a header.
     */
    public static function variantAction(string $name = 'pressVariant'): Action
    {
        return SwitchVariantAction::make(static::get(), $name);
    }

    /**
     * @internal
     */
    public function getId(): string
    {
        return 'press-filament-theme';
    }

    public function variant(PressVariant $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function broadsheet(): static
    {
        return $this->variant(PressVariant::Broadsheet);
    }

    public function telex(): static
    {
        return $this->variant(PressVariant::Telex);
    }

    public function gutter(): static
    {
        return $this->variant(PressVariant::Gutter);
    }

    public function vellum(): static
    {
        return $this->variant(PressVariant::Vellum);
    }

    /**
     * The horizontal navigation Press is built around. Turning it off leaves the
     * panel on Filament's sidebar; the theme degrades to it on purpose.
     */
    public function rail(bool $condition = true): static
    {
        $this->rail = $condition;

        return $this;
    }

    public function applyTheme(bool $condition = true): static
    {
        $this->applyTheme = $condition;

        return $this;
    }

    /**
     * Whether to put the switcher in the user menu. Turn it off to place it
     * yourself with PressFilamentTheme::variantAction().
     */
    public function switcherUi(bool $condition = true): static
    {
        $this->switcherUi = $condition;

        return $this;
    }

    public function switcherVisible(bool | Closure $condition = true): static
    {
        $this->switcherVisible = $condition;

        return $this;
    }

    /**
     * @internal
     */
    public function isSwitcherVisible(): bool
    {
        return (bool) (is_callable($this->switcherVisible)
            ? ($this->switcherVisible)()
            : $this->switcherVisible);
    }

    /**
     * @internal
     */
    public function getVariant(): PressVariant
    {
        return $this->variant;
    }

    /**
     * @internal
     */
    public function hasRail(): bool
    {
        return $this->rail;
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register(
            assets: [
                ...array_map(
                    fn (PressVariant $variant): Theme => Theme::make(
                        $variant->getThemeId(),
                        __DIR__ . '/../resources/dist/' . $variant->getThemeId() . '.css',
                    ),
                    PressVariant::cases(),
                ),
                // The presets on their own, for panels that compile their own theme.
                // loadedOnRequest keeps them out of head until a render hook asks.
                ...array_map(
                    fn (PressVariant $variant): Css => Css::make(
                        $variant->getPresetId(),
                        __DIR__ . '/../resources/css/presets/' . $variant->value . '.css',
                    )->loadedOnRequest(),
                    PressVariant::cases(),
                ),
                Js::make('press-rail', __DIR__ . '/../resources/js/rail.js'),
            ],
            package: static::PACKAGE,
        );

        if ($this->rail) {
            $panel->topNavigation();
        }

        $this->registerFonts($panel);

        if ($this->runtimeSwitch && $this->switcherUi) {
            $panel->userMenuItems([SwitchVariantAction::make($this)]);
        }

        if ($this->runtimeSwitch) {
            $this->registerPresetOverride($panel);
        }
    }

    public function boot(Panel $panel): void
    {
        if (! $this->shouldApplyTheme($panel)) {
            return;
        }

        $panel->theme(
            $this->runtimeSwitch
                ? Theme::make('press-runtime')->html(fn (): string => $this->getThemeLinkHtml())
                : $this->variant->getThemeId()
        );
    }

    /**
     * Theme::html() is resolved through value() when the layout renders, which is
     * the only moment the panel can see the request. Panel::theme() itself takes no
     * Closure, so the Theme object carries it instead.
     */
    protected function getThemeLinkHtml(): string
    {
        $href = FilamentAsset::getTheme($this->resolveVariant()->getThemeId())?->getHref();

        return '<link rel="stylesheet" href="' . e($href ?? '') . '" data-navigate-track />';
    }

    /**
     * When the app compiles its own theme, its stylesheet already carries one preset.
     * STYLES_AFTER lands after it, so the chosen preset wins on document order.
     *
     * Registered here rather than in boot(): Panel::boot() hands its render hooks to
     * FilamentView before it boots its plugins, so a hook added later never arrives.
     * Whether to emit anything is decided inside the closure, at render time, when
     * viteTheme() has been set.
     */
    protected function registerPresetOverride(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::STYLES_AFTER,
            function () use ($panel): HtmlString {
                if ($this->shouldApplyTheme($panel)) {
                    return new HtmlString('');
                }

                $href = FilamentAsset::getStyleHref(
                    $this->resolveVariant()->getPresetId(),
                    static::PACKAGE,
                );

                return new HtmlString('<link rel="stylesheet" href="' . e($href) . '" data-navigate-track />');
            },
        );
    }

    protected function registerFonts(Panel $panel): void
    {
        if (! $this->runtimeSwitch) {
            $panel->font($this->variant->getSansFont(), provider: BunnyFontProvider::class);
            $panel->monoFont($this->variant->getMonoFont(), provider: BunnyFontProvider::class);

            if (filled($serifFont = $this->variant->getSerifFont())) {
                $panel->serifFont(
                    $serifFont,
                    url: $this->variant->getSerifFontUrl(),
                    provider: BunnyFontProvider::class,
                );
            }

            return;
        }

        // Closures here are evaluated inside <head> on every render, so the fonts
        // follow whichever edition the request resolved to.
        $panel->font(fn (): string => $this->resolveVariant()->getSansFont(), provider: BunnyFontProvider::class);
        $panel->monoFont(fn (): string => $this->resolveVariant()->getMonoFont(), provider: BunnyFontProvider::class);
        $panel->serifFont(
            fn (): ?string => $this->resolveVariant()->getSerifFont(),
            url: fn (): ?string => $this->resolveVariant()->getSerifFontUrl(),
            provider: BunnyFontProvider::class,
        );
    }

    /**
     * Deliberately resolved in boot() rather than register(): register() runs
     * halfway through the panel's fluent chain, so viteTheme() may not be set yet.
     */
    protected function shouldApplyTheme(Panel $panel): bool
    {
        if ($this->applyTheme !== null) {
            return $this->applyTheme;
        }

        return blank($panel->getViteTheme());
    }
}
