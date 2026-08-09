<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'edgar-ferreira'));
});

/**
 * Use the About portrait as the author avatar.
 *
 * The React app pointed article bylines at a hosted headshot; here the same
 * image the About section uses stands in for a Gravatar, which the site owner
 * is unlikely to have configured.
 *
 * @return array
 */
add_filter('pre_get_avatar_data', function ($args) {
    if (! empty($args['url'])) {
        return $args;
    }

    $portraitId = Content::option('about_image');

    if (! $portraitId) {
        return $args;
    }

    $size = $args['size'] ?? 96;
    $url = wp_get_attachment_image_url((int) $portraitId, [$size, $size]);

    if ($url) {
        $args['url'] = $url;
        $args['found_avatar'] = true;
    }

    return $args;
});
