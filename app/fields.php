<?php

/**
 * Secure Custom Fields definitions.
 *
 * Field groups are registered in code rather than through the admin UI so the
 * schema lives in version control and deploys with the theme.
 */

namespace App;

/**
 * Bail out with an admin notice when SCF is not active — every template below
 * reads its content through get_field().
 *
 * @return bool
 */
function has_scf(): bool
{
    return function_exists('acf_add_local_field_group');
}

add_action('admin_notices', function () {
    if (has_scf()) {
        return;
    }

    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html__(
            'Secure Custom Fields is not active. Run "composer require wp-plugin/secure-custom-fields" and activate the plugin — the theme reads all of its content from it.',
            'edgar-ferreira'
        )
    );
});

/**
 * Icon choices shared by every social link repeater.
 *
 * @return array<string, string>
 */
function icon_choices(): array
{
    return [
        'github' => 'GitHub',
        'linkedin' => 'LinkedIn',
        'send' => 'Send',
        'mail' => 'Mail',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
    ];
}

/**
 * Build a repeater whose rows hold a single text value.
 *
 * The markdown front matter used plain string arrays for these (skills,
 * features, technologies…); a one-column repeater is the closest SCF shape.
 *
 * @return array
 */
function text_list_field(string $key, string $name, string $label, string $button = 'Add Item'): array
{
    return [
        'key' => $key,
        'label' => $label,
        'name' => $name,
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => $button,
        'sub_fields' => [
            [
                'key' => "{$key}_value",
                'label' => 'Value',
                'name' => 'value',
                'type' => 'text',
                'required' => 1,
            ],
        ],
    ];
}

/**
 * Build a social links repeater.
 *
 * @return array
 */
function social_links_field(string $key, string $name): array
{
    return [
        'key' => $key,
        'label' => 'Social Links',
        'name' => $name,
        'type' => 'repeater',
        'layout' => 'table',
        'button_label' => 'Add Link',
        'sub_fields' => [
            [
                'key' => "{$key}_icon",
                'label' => 'Icon',
                'name' => 'icon',
                'type' => 'select',
                'choices' => icon_choices(),
                'default_value' => 'github',
                'wrapper' => ['width' => '20'],
            ],
            [
                'key' => "{$key}_label",
                'label' => 'Label',
                'name' => 'label',
                'type' => 'text',
                'required' => 1,
                'wrapper' => ['width' => '30'],
            ],
            [
                'key' => "{$key}_href",
                'label' => 'URL',
                'name' => 'href',
                'type' => 'text',
                'required' => 1,
                'instructions' => 'Accepts mailto: and tel: links as well as URLs.',
                'wrapper' => ['width' => '50'],
            ],
        ],
    ];
}

add_action('acf/init', function () {
    if (! has_scf()) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Site Content', 'edgar-ferreira'),
        'menu_title' => __('Site Content', 'edgar-ferreira'),
        'menu_slug' => 'site-content',
        'capability' => 'edit_theme_options',
        'position' => 4,
        'icon_url' => 'dashicons-admin-customizer',
        'redirect' => false,
        'update_button' => __('Save Content', 'edgar-ferreira'),
    ]);

    /*
     * Front page content. One group with a tab per section, mirroring the
     * files that lived in base/content/en/pages/.
     */
    acf_add_local_field_group([
        'key' => 'group_site_content',
        'title' => __('Site Content', 'edgar-ferreira'),
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'site-content',
                ],
            ],
        ],
        'fields' => [
            // ---------------------------------------------------------- Hero
            [
                'key' => 'field_tab_hero',
                'label' => __('Hero', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_hero_title_left',
                'label' => 'Title (left)',
                'name' => 'hero_title_left',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_hero_title_right',
                'label' => 'Title (right)',
                'name' => 'hero_title_right',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_hero_description',
                'label' => 'Description',
                'name' => 'hero_description',
                'type' => 'textarea',
                'rows' => 4,
            ],
            [
                'key' => 'field_hero_cta_label',
                'label' => 'CTA Label',
                'name' => 'hero_cta_label',
                'type' => 'text',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_hero_cta_href',
                'label' => 'CTA Link',
                'name' => 'hero_cta_href',
                'type' => 'text',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_hero_carousel_label',
                'label' => 'Carousel Label',
                'name' => 'hero_carousel_label',
                'type' => 'text',
                'wrapper' => ['width' => '34'],
            ],
            social_links_field('field_hero_social_links', 'hero_social_links'),

            // --------------------------------------------------------- About
            [
                'key' => 'field_tab_about',
                'label' => __('About', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_about_title',
                'label' => 'Section Title',
                'name' => 'about_title',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_about_image',
                'label' => 'Portrait',
                'name' => 'about_image',
                'type' => 'image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_about_greeting',
                'label' => 'Greeting',
                'name' => 'about_greeting',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_about_name',
                'label' => 'Name',
                'name' => 'about_name',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_about_role_summary',
                'label' => 'Role Summary',
                'name' => 'about_role_summary',
                'type' => 'textarea',
                'rows' => 3,
                'instructions' => 'Completes the sentence that follows your name.',
            ],
            [
                'key' => 'field_about_experience_years',
                'label' => 'Experience Years',
                'name' => 'about_experience_years',
                'type' => 'text',
                'instructions' => 'Rendered in bold after "With over".',
            ],
            [
                'key' => 'field_about_experience_text',
                'label' => 'Experience Text',
                'name' => 'about_experience_text',
                'type' => 'textarea',
                'rows' => 4,
            ],
            [
                'key' => 'field_about_principles_text',
                'label' => 'Principles Text',
                'name' => 'about_principles_text',
                'type' => 'textarea',
                'rows' => 4,
            ],
            text_list_field('field_about_skills', 'about_skills', 'Technologies & Tools', 'Add Skill'),
            text_list_field('field_about_what_i_do', 'about_what_i_do', 'What I Do', 'Add Item'),
            [
                'key' => 'field_about_education_heading',
                'label' => 'Education Heading',
                'name' => 'about_education_heading',
                'type' => 'text',
            ],
            text_list_field('field_about_education', 'about_education', 'Education', 'Add Entry'),
            [
                'key' => 'field_about_certifications_heading',
                'label' => 'Certifications Heading',
                'name' => 'about_certifications_heading',
                'type' => 'text',
            ],
            text_list_field('field_about_certifications', 'about_certifications', 'Certifications', 'Add Entry'),

            // ---------------------------------------------------- Experience
            [
                'key' => 'field_tab_work',
                'label' => __('Experience', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_work_heading',
                'label' => 'Heading',
                'name' => 'work_heading',
                'type' => 'text',
            ],
            [
                'key' => 'field_work_experiences',
                'label' => 'Experiences',
                'name' => 'work_experiences',
                'type' => 'repeater',
                'layout' => 'row',
                'button_label' => 'Add Experience',
                'sub_fields' => [
                    [
                        'key' => 'field_work_period',
                        'label' => 'Period',
                        'name' => 'period',
                        'type' => 'text',
                        'wrapper' => ['width' => '25'],
                    ],
                    [
                        'key' => 'field_work_duration',
                        'label' => 'Duration',
                        'name' => 'duration',
                        'type' => 'text',
                        'wrapper' => ['width' => '25'],
                    ],
                    [
                        'key' => 'field_work_company',
                        'label' => 'Company',
                        'name' => 'company',
                        'type' => 'text',
                        'wrapper' => ['width' => '50'],
                    ],
                    [
                        'key' => 'field_work_position',
                        'label' => 'Position',
                        'name' => 'position',
                        'type' => 'textarea',
                        'rows' => 2,
                    ],
                ],
            ],
            [
                'key' => 'field_work_summary_label',
                'label' => 'Summary Label',
                'name' => 'work_summary_label',
                'type' => 'text',
                'wrapper' => ['width' => '40'],
            ],
            [
                'key' => 'field_work_summary_value',
                'label' => 'Summary Value',
                'name' => 'work_summary_value',
                'type' => 'text',
                'wrapper' => ['width' => '60'],
            ],

            // ------------------------------------------------ Projects intro
            [
                'key' => 'field_tab_projects',
                'label' => __('Projects', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_projects_title',
                'label' => 'Section Title',
                'name' => 'projects_title',
                'type' => 'text',
                'instructions' => 'Projects themselves are managed under Projects in the sidebar.',
            ],
            [
                'key' => 'field_projects_cta_label',
                'label' => 'Footer Link Label',
                'name' => 'projects_cta_label',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_projects_cta_href',
                'label' => 'Footer Link URL',
                'name' => 'projects_cta_href',
                'type' => 'text',
                'wrapper' => ['width' => '50'],
            ],

            // ------------------------------------------------ Articles intro
            [
                'key' => 'field_tab_articles',
                'label' => __('Articles', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_articles_heading',
                'label' => 'Heading',
                'name' => 'articles_heading',
                'type' => 'text',
                'instructions' => 'Articles themselves are the standard Posts.',
            ],

            // ------------------------------------------------------- Contact
            [
                'key' => 'field_tab_contact',
                'label' => __('Contact', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_contact_title',
                'label' => 'Section Title',
                'name' => 'contact_title',
                'type' => 'text',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_contact_name',
                'label' => 'Name',
                'name' => 'contact_name',
                'type' => 'text',
                'instructions' => 'Each word is rendered on its own line.',
                'wrapper' => ['width' => '33'],
            ],
            [
                'key' => 'field_contact_title_suffix',
                'label' => 'Role / Location',
                'name' => 'contact_title_suffix',
                'type' => 'text',
                'wrapper' => ['width' => '34'],
            ],
            [
                'key' => 'field_contact_info',
                'label' => 'Contact Info',
                'name' => 'contact_info',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Add Row',
                'sub_fields' => [
                    [
                        'key' => 'field_contact_info_label',
                        'label' => 'Label',
                        'name' => 'label',
                        'type' => 'text',
                        'wrapper' => ['width' => '40'],
                    ],
                    [
                        'key' => 'field_contact_info_value',
                        'label' => 'Value',
                        'name' => 'value',
                        'type' => 'text',
                        'wrapper' => ['width' => '60'],
                    ],
                ],
            ],
            social_links_field('field_contact_social_links', 'contact_social_links'),
            [
                'key' => 'field_contact_footer',
                'label' => 'Footer Text',
                'name' => 'contact_footer',
                'type' => 'text',
                'instructions' => 'Use {year} for the current year.',
            ],

            // ------------------------------------------------------ Analytics
            [
                'key' => 'field_tab_analytics',
                'label' => __('Analytics', 'edgar-ferreira'),
                'type' => 'tab',
                'placement' => 'left',
            ],
            [
                'key' => 'field_analytics_umami_src',
                'label' => 'Umami Script URL',
                'name' => 'analytics_umami_src',
                'type' => 'text',
                'instructions' => 'Leave empty to disable analytics.',
            ],
            [
                'key' => 'field_analytics_umami_id',
                'label' => 'Umami Website ID',
                'name' => 'analytics_umami_id',
                'type' => 'text',
            ],
        ],
    ]);

    /*
     * Per-project fields. Title, body, featured image and tags use the native
     * WordPress equivalents, so only the extras live here.
     */
    acf_add_local_field_group([
        'key' => 'group_project_details',
        'title' => __('Project Details', 'edgar-ferreira'),
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'project',
                ],
            ],
        ],
        'fields' => [
            [
                'key' => 'field_project_description',
                'label' => 'Short Description',
                'name' => 'description',
                'type' => 'textarea',
                'rows' => 3,
                'required' => 1,
                'instructions' => 'Shown on the hero carousel and the projects list.',
            ],
            [
                'key' => 'field_project_image_url',
                'label' => 'External Image URL',
                'name' => 'image_url',
                'type' => 'url',
                'instructions' => 'Fallback used only when no featured image is set.',
            ],
            [
                'key' => 'field_project_demo_url',
                'label' => 'Demo URL',
                'name' => 'demo_url',
                'type' => 'url',
                'wrapper' => ['width' => '50'],
            ],
            [
                'key' => 'field_project_github_url',
                'label' => 'Repository URL',
                'name' => 'github_url',
                'type' => 'url',
                'wrapper' => ['width' => '50'],
            ],
            text_list_field('field_project_features', 'features', 'Key Features', 'Add Feature'),
            text_list_field('field_project_technologies', 'technologies', 'Technologies Used', 'Add Technology'),
        ],
    ]);

    /*
     * Article extras. Posts already carry title, body, excerpt, tags and date.
     */
    acf_add_local_field_group([
        'key' => 'group_article_meta',
        'title' => __('Article Details', 'edgar-ferreira'),
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'post',
                ],
            ],
        ],
        'fields' => [
            [
                'key' => 'field_article_read_time',
                'label' => 'Read Time',
                'name' => 'read_time',
                'type' => 'text',
                'placeholder' => '5 min read',
            ],
        ],
    ]);
});
