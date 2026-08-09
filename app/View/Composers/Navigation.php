<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Navigation extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.navigation',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'navLinks' => $this->navLinks(),
        ];
    }

    /**
     * Links for the header.
     *
     * Uses the Primary Navigation menu when one is assigned, otherwise falls
     * back to the section anchors the React app hard-coded.
     *
     * @return array<int, array{label: string, href: string}>
     */
    protected function navLinks(): array
    {
        $items = has_nav_menu('primary_navigation')
            ? wp_get_nav_menu_items(get_nav_menu_locations()['primary_navigation'])
            : [];

        if (! empty($items)) {
            return array_map(fn ($item) => [
                'label' => $item->title,
                'href' => $item->url,
            ], $items);
        }

        return [
            ['label' => __('About', 'edgar-ferreira'), 'href' => home_url('/#about')],
            ['label' => __('Projects', 'edgar-ferreira'), 'href' => home_url('/#projects')],
            ['label' => __('Articles', 'edgar-ferreira'), 'href' => home_url('/#articles')],
            ['label' => __('Contact', 'edgar-ferreira'), 'href' => home_url('/#contact')],
        ];
    }
}
