<?php

/**
 * Custom post types and taxonomies.
 */

namespace App;

/**
 * Register the Projects post type and its tag taxonomy.
 *
 * The `project` slug keeps permalinks at /project/{slug}, matching the
 * /project/:id route from the React app.
 *
 * @return void
 */
add_action('init', function () {
    register_post_type('project', [
        'labels' => [
            'name' => __('Projects', 'edgar-ferreira'),
            'singular_name' => __('Project', 'edgar-ferreira'),
            'add_new_item' => __('Add New Project', 'edgar-ferreira'),
            'edit_item' => __('Edit Project', 'edgar-ferreira'),
            'new_item' => __('New Project', 'edgar-ferreira'),
            'view_item' => __('View Project', 'edgar-ferreira'),
            'search_items' => __('Search Projects', 'edgar-ferreira'),
            'not_found' => __('No projects found', 'edgar-ferreira'),
            'all_items' => __('All Projects', 'edgar-ferreira'),
            'menu_name' => __('Projects', 'edgar-ferreira'),
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-portfolio',
        'menu_position' => 5,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'project',
            'with_front' => false,
        ],
        'supports' => [
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'revisions',
            'page-attributes',
        ],
    ]);

    register_taxonomy('project_tag', 'project', [
        'labels' => [
            'name' => __('Project Tags', 'edgar-ferreira'),
            'singular_name' => __('Project Tag', 'edgar-ferreira'),
            'search_items' => __('Search Project Tags', 'edgar-ferreira'),
            'all_items' => __('All Project Tags', 'edgar-ferreira'),
            'edit_item' => __('Edit Project Tag', 'edgar-ferreira'),
            'add_new_item' => __('Add New Project Tag', 'edgar-ferreira'),
            'menu_name' => __('Tags', 'edgar-ferreira'),
        ],
        'public' => true,
        'hierarchical' => false,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'project-tag',
            'with_front' => false,
        ],
    ]);
});

/**
 * Order projects by menu order in the admin, mirroring the `order` field the
 * markdown front matter used.
 *
 * @return void
 */
add_action('pre_get_posts', function ($query) {
    if (! is_admin() || ! $query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'project' && ! $query->get('orderby')) {
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
});
