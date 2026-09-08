<?php

namespace Elemind\PressFilamentTheme;

use Elemind\PressFilamentTheme\Enums\PressVariant;
use Filament\Contracts\Plugin;
use Filament\FontProviders\BunnyFontProvider;
use Filament\Panel;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;

class PressFilamentTheme implements Plugin
{
    public const PACKAGE = 'elemind/press-filament-theme';

    protected PressVariant $variant = PressVariant::Broadsheet;

    protected bool $rail = true;

    /**
     * null means "decide at boot": the theme is applied unless the panel already
     * compiles its own with viteTheme(), in which case the app is expected to
     * import the Press sources into that file instead.
     */
    protected ?bool $applyTheme = null;

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

    public function getVariant(): PressVariant
    {
        return $this->variant;
    }

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
                Js::make('press-rail', __DIR__ . '/../resources/js/rail.js'),
            ],
            package: static::PACKAGE,
        );

        if ($this->rail) {
            $panel->topNavigation();
        }

        $panel->font($this->variant->getSansFont(), provider: BunnyFontProvider::class);
        $panel->monoFont($this->variant->getMonoFont(), provider: BunnyFontProvider::class);

        if (filled($serifFont = $this->variant->getSerifFont())) {
            $panel->serifFont(
                $serifFont,
                url: $this->variant->getSerifFontUrl(),
                provider: BunnyFontProvider::class,
            );
        }
    }

    public function boot(Panel $panel): void
    {
        if ($this->shouldApplyTheme($panel)) {
            $panel->theme($this->variant->getThemeId());
        }
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
