# Changelog

All notable changes to `press-filament-theme` will be documented in this file.

## v1.0.2 - 2026-09-09

**Full Changelog**: https://github.com/elemind/press-filament-theme/compare/v1.0.1...v1.0.2

## v1.0.1 - 2026-09-08

### Fixed

- The installation order in the README. Press registers its stylesheets and its rail script from
  the plugin, so `php artisan filament:assets` has to run *after* the plugin is registered on a
  panel — run before, it publishes nothing and reports no error. The three sections that gave the
  old order now agree.

## v1.0.0 - 2026-09-08

### Added

- Four editions — Broadsheet, Telex, Gutter and Vellum — picked with `->variant()` or the
  shortcut named after each one, defaulting to Broadsheet.
- Two ways to install: the compiled stylesheet that ships with the package, or the engine and
  presets imported into a panel that compiles its own theme. The plugin detects `viteTheme()`
  and stands down.
- The rail: the horizontal navigation the theme is built around, on by default, turned off
  with `->rail(false)`.
- `->runtimeSwitch()`, off by default: a "Theme" entry in the user menu that lets people pick
  their own edition. The choice lives in a cookie for a year, is read for guests too, and can
  be routed elsewhere with `->variantResolver()` and `->variantPersister()`.
- `PressFilamentTheme::variantAction()`, for placing the switcher outside the user menu.
