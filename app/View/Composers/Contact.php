<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class Contact extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.contact',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'contact' => Content::contact(),
        ];
    }
}
