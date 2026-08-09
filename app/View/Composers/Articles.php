<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class Articles extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.articles',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'articles' => Content::articles(),
            'section' => Content::articlesSection(),
        ];
    }
}
