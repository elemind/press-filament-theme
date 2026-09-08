<?php

namespace Elemind\PressFilamentTheme\Enums;

enum PressVariant: string
{
    case Broadsheet = 'broadsheet';

    case Telex = 'telex';

    case Gutter = 'gutter';

    case Vellum = 'vellum';

    /**
     * The id the compiled theme is registered under in FilamentAsset.
     *
     * @internal
     */
    public function getThemeId(): string
    {
        return 'press-' . $this->value;
    }

    /**
     * The id the preset alone is registered under, without the engine around it.
     *
     * @internal
     */
    public function getPresetId(): string
    {
        return 'press-preset-' . $this->value;
    }

    /**
     * @internal
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Broadsheet => 'Broadsheet',
            self::Telex => 'Telex',
            self::Gutter => 'Gutter',
            self::Vellum => 'Vellum',
        };
    }

    /**
     * @internal
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::Broadsheet => 'Masthead red on warm neutrals, serif headings under a double rule',
            self::Telex => 'Phosphor green on cold graphite, monospace headings, a bar that stays dark',
            self::Gutter => 'Ultramarine on pure neutrals, no rounded corners, drawn borders',
            self::Vellum => 'Iris on violet neutrals, generous radii, glass and light',
        };
    }

    /**
     * @internal
     */
    public function getSansFont(): string
    {
        return match ($this) {
            self::Broadsheet => 'Instrument Sans',
            self::Telex => 'IBM Plex Sans',
            self::Gutter => 'Archivo',
            self::Vellum => 'Figtree',
        };
    }

    /**
     * Only Broadsheet uses a serif face; the others fall back to the browser default.
     *
     * @internal
     */
    public function getSerifFont(): ?string
    {
        return match ($this) {
            self::Broadsheet => 'Instrument Serif',
            default => null,
        };
    }

    /**
     * @internal
     */
    public function getMonoFont(): string
    {
        return 'IBM Plex Mono';
    }

    /**
     * Bunny serves Instrument Serif at weight 400 only, while Filament's provider
     * asks for 400,500,600,700 by default and would get nothing back.
     *
     * @internal
     */
    public function getSerifFontUrl(): ?string
    {
        return match ($this) {
            self::Broadsheet => 'https://fonts.bunny.net/css?family=instrument-serif:400&display=swap',
            default => null,
        };
    }
}
