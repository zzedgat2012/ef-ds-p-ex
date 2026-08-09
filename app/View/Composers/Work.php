<?php

namespace App\View\Composers;

use App\Content;
use Roots\Acorn\View\Composer;

class Work extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.work',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'work' => Content::work(),
            'businessEmail' => $this->businessEmail(),
        ];
    }

    /**
     * Address the experience rows open a work-declaration request to.
     */
    protected function businessEmail(): string
    {
        foreach (Content::contact()['contactInfo'] as $row) {
            if (\in_array($row['label'], ['Business Email', 'Email (Business)'], true)) {
                return $row['value'];
            }
        }

        return (string) get_option('admin_email');
    }
}
