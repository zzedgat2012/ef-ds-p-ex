<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class Hero extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.hero',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'hero' => Content::hero(),
            'projects' => Content::projects(),
        ];
    }
}
