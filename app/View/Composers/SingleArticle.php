<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;
use WP_Post;

class SingleArticle extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        $post = get_queried_object();

        return [
            'article' => $post instanceof WP_Post ? Content::article($post) : null,
        ];
    }
}
