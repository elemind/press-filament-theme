<?php

/**
 * Reads the four edition stylesheets and returns the rail tokens each one
 * declares, split by cascade bucket.
 *
 * @return array<string, array{light: list<string>, dark: list<string>}>
 */
function pressRailTokens(): array
{
    $presets = [];

    foreach (glob(__DIR__ . '/../resources/css/presets/*.css') as $path) {
        $css = preg_replace('#/\*.*?\*/#s', '', (string) file_get_contents($path));

        $buckets = ['light' => [], 'dark' => []];

        preg_match_all('/([^{}]+)\{([^{}]*)\}/', (string) $css, $rules, PREG_SET_ORDER);

        foreach ($rules as $rule) {
            $bucket = str_contains($rule[1], '.dark') ? 'dark' : 'light';

            preg_match_all('/(--press-rail-[a-z0-9-]+)\s*:/', $rule[2], $matches);

            $buckets[$bucket] = [...$buckets[$bucket], ...$matches[1]];
        }

        $presets[basename($path, '.css')] = [
            'light' => array_values(array_unique($buckets['light'])),
            'dark' => array_values(array_unique($buckets['dark'])),
        ];
    }

    ksort($presets);

    return $presets;
}

it('parses rail tokens out of every edition', function () {
    $presets = pressRailTokens();

    expect(array_keys($presets))->toBe(['broadsheet', 'gutter', 'telex', 'vellum']);

    foreach ($presets as $edition => $buckets) {
        expect($buckets['light'])->not->toBeEmpty("{$edition} declares no rail token at all");
    }
});

/**
 * An edition that stays silent on a token another edition sets does not fall
 * back to the engine: it inherits the other edition's value, because a compiled
 * theme bakes one edition into `:root` and the runtime switch overlays a second
 * one after it. The two buckets are checked apart because `:root` and `.dark`
 * carry the same specificity, so a token declared only in `:root` silently
 * kills the engine's `.dark` rule for it.
 *
 * Scoped to the tokens that caused a real regression. Roughly twenty more holes
 * of the same shape are still open across the four editions; widening the guard
 * to every `--press-rail-*` token is tracked in issue #2.
 */
it('declares a guarded rail token in every edition or in none', function (string $token, string $bucket) {
    $presets = pressRailTokens();

    $declaring = [];
    $silent = [];

    foreach ($presets as $edition => $buckets) {
        in_array($token, $buckets[$bucket], true)
            ? $declaring[] = $edition
            : $silent[] = $edition;
    }

    $this->assertTrue(
        $declaring === [] || $silent === [],
        sprintf(
            "%s is declared in the `%s` bucket by %s but not by %s.\n" .
            'Either every edition declares it there, or none does: a partial set leaks ' .
            'the declaring edition’s value into the silent ones when the runtime switch ' .
            'overlays a preset on top of a compiled theme.',
            $token,
            $bucket === 'dark' ? '.dark' : ':root',
            implode(', ', $declaring),
            implode(', ', $silent),
        ),
    );
})
    ->with([
        '--press-rail-logo-filter',
        '--press-rail-logo-color',
        '--press-rail-item-font',
        '--press-rail-shadow',
    ])
    ->with(['light', 'dark']);
