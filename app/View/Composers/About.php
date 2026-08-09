<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class About extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.about',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'about' => Content::about(),
        ];
    }
}
