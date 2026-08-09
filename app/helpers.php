<?php

/**
 * Template helpers.
 */

namespace App;

/**
 * Map a utility class to the group it competes in.
 *
 * Mirrors the subset of tailwind-merge's conflict groups that the ported
 * components actually rely on.
 */
function class_group(string $class): string
{
    if (str_starts_with($class, 'hover:')) {
        return 'hover:'.class_group(substr($class, 6));
    }

    if (str_starts_with($class, 'focus-visible:')) {
        return 'focus-visible:'.class_group(substr($class, 14));
    }

    $textSizes = ['xs', 'sm', 'base', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl'];

    return match (true) {
        // text-sm is a font size; text-accent is a colour. They do not conflict.
        str_starts_with($class, 'text-') => \in_array(substr($class, 5), $textSizes, true)
            ? 'font-size'
            : 'text-color',
        str_starts_with($class, 'rounded-'), $class === 'rounded' => 'rounded',
        str_starts_with($class, 'bg-') => 'bg',
        str_starts_with($class, 'font-') => 'font',
        str_starts_with($class, 'px-') => 'px',
        str_starts_with($class, 'py-') => 'py',
        str_starts_with($class, 'p-') => 'p',
        str_starts_with($class, 'h-') => 'h',
        str_starts_with($class, 'w-') => 'w',
        str_starts_with($class, 'gap-') => 'gap',
        str_starts_with($class, 'shadow-') => 'shadow',
        default => $class,
    };
}

/**
 * Merge Tailwind class strings, letting later classes win their group.
 *
 * The React components leaned on cn()/tailwind-merge for this — without it a
 * caller's `rounded-full` would not reliably beat a variant's `rounded-md`,
 * since CSS order rather than attribute order decides the winner.
 */
function cn(?string ...$classes): string
{
    $resolved = [];

    foreach ($classes as $group) {
        if (! $group) {
            continue;
        }

        foreach (preg_split('/\s+/', trim($group)) as $class) {
            if ($class === '') {
                continue;
            }

            $resolved[class_group($class)] = $class;
        }
    }

    return implode(' ', array_values($resolved));
}
