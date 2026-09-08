<?php

namespace Elemind\PressFilamentTheme\Concerns;

use Closure;
use Elemind\PressFilamentTheme\Enums\PressVariant;
use Illuminate\Support\Facades\Cookie;

trait ResolvesVariant
{
    public const COOKIE = 'press_variant';

    public const COOKIE_MINUTES = 60 * 24 * 365;

    protected bool $runtimeSwitch = false;

    /**
     * @var array<int, PressVariant>|null
     */
    protected ?array $availableVariants = null;

    protected ?Closure $variantResolver = null;

    protected ?Closure $variantPersister = null;

    /**
     * Let people pick their own edition. Pass a list to offer only some of them.
     *
     * @param  bool|array<int, PressVariant>  $condition
     */
    public function runtimeSwitch(bool | array $condition = true): static
    {
        if (is_array($condition)) {
            $this->availableVariants = array_values($condition);
            $this->runtimeSwitch = $condition !== [];

            return $this;
        }

        $this->runtimeSwitch = $condition;

        return $this;
    }

    /**
     * Where the choice comes from. Without one, it comes from the cookie.
     */
    public function variantResolver(?Closure $callback): static
    {
        $this->variantResolver = $callback;

        return $this;
    }

    /**
     * Where the choice goes. Without one, it goes to the cookie.
     */
    public function variantPersister(?Closure $callback): static
    {
        $this->variantPersister = $callback;

        return $this;
    }

    public function hasRuntimeSwitch(): bool
    {
        return $this->runtimeSwitch;
    }

    /**
     * @return array<int, PressVariant>
     */
    public function getAvailableVariants(): array
    {
        return $this->availableVariants ?? PressVariant::cases();
    }

    /**
     * The edition someone gets before they have chosen one. A default outside the
     * offered list would be unreachable from the switcher, so the list wins.
     */
    public function getDefaultVariant(): PressVariant
    {
        $available = $this->getAvailableVariants();

        if (in_array($this->variant, $available, true)) {
            return $this->variant;
        }

        return $available[0] ?? $this->variant;
    }

    /**
     * Resolved at render time, where the request and the authenticated user exist.
     */
    public function resolveVariant(): PressVariant
    {
        if (! $this->runtimeSwitch) {
            return $this->variant;
        }

        $stored = $this->variantResolver
            ? ($this->variantResolver)()
            : request()->cookie(static::COOKIE);

        $variant = match (true) {
            $stored instanceof PressVariant => $stored,
            is_string($stored) => PressVariant::tryFrom($stored),
            default => null,
        };

        if ($variant === null || ! in_array($variant, $this->getAvailableVariants(), true)) {
            return $this->getDefaultVariant();
        }

        return $variant;
    }

    public function persistVariant(PressVariant $variant): void
    {
        if ($this->variantPersister) {
            ($this->variantPersister)($variant);

            return;
        }

        Cookie::queue(static::COOKIE, $variant->value, static::COOKIE_MINUTES);
    }
}
