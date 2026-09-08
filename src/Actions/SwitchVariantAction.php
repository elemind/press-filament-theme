<?php

namespace Elemind\PressFilamentTheme\Actions;

use Elemind\PressFilamentTheme\Enums\PressVariant;
use Elemind\PressFilamentTheme\PressFilamentTheme;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

class SwitchVariantAction
{
    public static function make(PressFilamentTheme $plugin, string $name = 'pressVariant'): Action
    {
        return Action::make($name)
            ->label('Theme')
            ->icon(Heroicon::Swatch)
            ->modalHeading('Choose a theme')
            ->modalSubmitActionLabel('Apply')
            ->visible(fn (): bool => $plugin->hasRuntimeSwitch() && $plugin->isSwitcherVisible())
            ->fillForm(fn (): array => ['variant' => $plugin->resolveVariant()->value])
            ->schema([
                Radio::make('variant')
                    ->hiddenLabel()
                    ->required()
                    ->options(fn (): array => static::mapVariants($plugin, fn (PressVariant $v) => $v->getLabel()))
                    ->descriptions(fn (): array => static::mapVariants($plugin, fn (PressVariant $v) => $v->getDescription())),
            ])
            ->action(function (array $data, Component $livewire) use ($plugin): void {
                $plugin->persistVariant(PressVariant::from($data['variant']));

                // navigate: false on purpose. Under ->spa() a wire:navigate redirect
                // leaves <head> untouched, so the previous stylesheet would survive.
                $livewire->redirect(
                    request()->header('Referer') ?? url()->current(),
                    navigate: false,
                );
            });
    }

    /**
     * @return array<string, string>
     */
    protected static function mapVariants(PressFilamentTheme $plugin, callable $value): array
    {
        $mapped = [];

        foreach ($plugin->getAvailableVariants() as $variant) {
            $mapped[$variant->value] = $value($variant);
        }

        return $mapped;
    }
}
