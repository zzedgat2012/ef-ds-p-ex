<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class Projects extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.projects',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'projects' => Content::projects(),
            'section' => Content::projectsSection(),
        ];
    }
}
