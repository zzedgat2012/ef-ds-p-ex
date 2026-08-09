<?php

namespace App;

use WP_Post;

/**
 * Reads site content out of Secure Custom Fields and reshapes it into the
 * arrays the Blade views consume.
 *
 * The shapes intentionally mirror the front matter of the markdown files the
 * React app read (base/content/en/), which keeps the templates a direct
 * translation of the original components.
 */
class Content
{
    /**
     * Read a single options-page field.
     */
    public static function option(string $name, mixed $default = null): mixed
    {
        if (! function_exists('get_field')) {
            return $default;
        }

        $value = get_field($name, 'option');

        return ($value === null || $value === false || $value === '') ? $default : $value;
    }

    /**
     * Read a field from a post, falling back when SCF is unavailable.
     */
    public static function field(string $name, int $postId, mixed $default = null): mixed
    {
        if (! function_exists('get_field')) {
            return $default;
        }

        $value = get_field($name, $postId);

        return ($value === null || $value === false || $value === '') ? $default : $value;
    }

    /**
     * Decode WordPress's HTML-encoded text for output through Blade.
     *
     * get_the_title(), get_the_excerpt() and term names come back already
     * encoded ("&" is stored as "&#038;"). Blade's {{ }} escapes again, which
     * renders the entity literally, so decode once here and let Blade do the
     * escaping — that keeps the values safe in attributes too.
     */
    public static function text(?string $value): string
    {
        return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Flatten a single-column repeater into a list of strings.
     *
     * @return array<int, string>
     */
    public static function textList(string $name, int|string $postId = 'option'): array
    {
        $rows = $postId === 'option'
            ? self::option($name, [])
            : self::field($name, (int) $postId, []);

        if (! \is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($row) => \is_array($row) ? trim((string) ($row['value'] ?? '')) : trim((string) $row),
            $rows
        ), fn ($value) => $value !== ''));
    }

    /**
     * Normalise a social links repeater.
     *
     * @return array<int, array{icon: string, label: string, href: string}>
     */
    public static function socialLinks(string $name): array
    {
        $rows = self::option($name, []);

        if (! \is_array($rows)) {
            return [];
        }

        return array_values(array_map(fn ($row) => [
            'icon' => $row['icon'] ?? 'github',
            'label' => $row['label'] ?? '',
            'href' => $row['href'] ?? '#',
        ], $rows));
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'titleLeft' => self::option('hero_title_left', ''),
            'titleRight' => self::option('hero_title_right', ''),
            'description' => self::option('hero_description', ''),
            'ctaLabel' => self::option('hero_cta_label', ''),
            'ctaHref' => self::option('hero_cta_href', '#projects'),
            'carouselLabel' => self::option('hero_carousel_label', ''),
            'socialLinks' => self::socialLinks('hero_social_links'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function about(): array
    {
        $imageId = self::option('about_image');

        return [
            'title' => self::option('about_title', ''),
            'image' => $imageId ? wp_get_attachment_image_url((int) $imageId, 'large') : '',
            'imageId' => $imageId ? (int) $imageId : null,
            'greeting' => self::option('about_greeting', ''),
            'name' => self::option('about_name', ''),
            'roleSummary' => self::option('about_role_summary', ''),
            'experienceYears' => self::option('about_experience_years', ''),
            'experienceText' => self::option('about_experience_text', ''),
            'principlesText' => self::option('about_principles_text', ''),
            'skills' => self::textList('about_skills'),
            'whatIDo' => self::textList('about_what_i_do'),
            'educationHeading' => self::option('about_education_heading', ''),
            'education' => self::textList('about_education'),
            'certificationsHeading' => self::option('about_certifications_heading', ''),
            'certifications' => self::textList('about_certifications'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function work(): array
    {
        $rows = self::option('work_experiences', []);

        return [
            'heading' => self::option('work_heading', ''),
            'experiences' => \is_array($rows) ? array_values(array_map(fn ($row) => [
                'period' => $row['period'] ?? '',
                'duration' => $row['duration'] ?? '',
                'company' => $row['company'] ?? '',
                'position' => $row['position'] ?? '',
            ], $rows)) : [],
            'summaryLabel' => self::option('work_summary_label', ''),
            'summaryValue' => self::option('work_summary_value', ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contact(): array
    {
        $rows = self::option('contact_info', []);

        return [
            'title' => self::option('contact_title', ''),
            'name' => self::option('contact_name', ''),
            'titleSuffix' => self::option('contact_title_suffix', ''),
            'contactInfo' => \is_array($rows) ? array_values(array_map(fn ($row) => [
                'label' => $row['label'] ?? '',
                'value' => $row['value'] ?? '',
            ], $rows)) : [],
            'socialLinks' => self::socialLinks('contact_social_links'),
            'footer' => str_replace(
                '{year}',
                wp_date('Y'),
                (string) self::option('contact_footer', '')
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function projectsSection(): array
    {
        return [
            'title' => self::option('projects_title', ''),
            'ctaLabel' => self::option('projects_cta_label', 'GitHub'),
            'ctaHref' => self::option('projects_cta_href', '#'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function articlesSection(): array
    {
        return [
            'heading' => self::option('articles_heading', ''),
        ];
    }

    /**
     * Per-request memo. The hero carousel and the projects section both render
     * the full project list, and the front page renders both.
     *
     * @var array<string, array>
     */
    protected static array $cache = [];

    /**
     * Every project, ordered the way the `order` front matter key ordered them.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function projects(): array
    {
        return self::$cache['projects'] ??= array_map([self::class, 'project'], get_posts([
            'post_type' => 'project',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => ['menu_order' => 'ASC', 'date' => 'ASC'],
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public static function project(WP_Post $post): array
    {
        $tags = wp_get_post_terms($post->ID, 'project_tag', ['fields' => 'names']);

        return [
            'id' => $post->post_name,
            'title' => self::text(get_the_title($post)),
            'description' => (string) self::field('description', $post->ID, ''),
            'longDescription' => apply_filters('the_content', $post->post_content),
            'tags' => array_map([self::class, 'text'], self::orderTags(is_wp_error($tags) ? [] : $tags, $post->ID)),
            'image' => self::projectImage($post),
            'permalink' => get_permalink($post),
            'demoUrl' => self::field('demo_url', $post->ID, ''),
            'githubUrl' => self::field('github_url', $post->ID, ''),
            'features' => self::textList('features', $post->ID),
            'technologies' => self::textList('technologies', $post->ID),
        ];
    }

    /**
     * Restore the authored tag order.
     *
     * wp_get_post_terms() sorts by name, but the design shows tags in the
     * order they were written. The importer records that order; any tag added
     * later in the admin is appended alphabetically after the known ones.
     *
     * @param  array<int, string>  $tags
     * @return array<int, string>
     */
    protected static function orderTags(array $tags, int $postId): array
    {
        $order = get_post_meta($postId, '_project_tag_order', true);

        if (! \is_array($order) || ! $order) {
            return $tags;
        }

        usort($tags, function ($a, $b) use ($order) {
            $left = array_search($a, $order, true);
            $right = array_search($b, $order, true);

            $left = $left === false ? PHP_INT_MAX : $left;
            $right = $right === false ? PHP_INT_MAX : $right;

            return $left === $right ? strcasecmp($a, $b) : $left <=> $right;
        });

        return $tags;
    }

    /**
     * Featured image, falling back to the external URL field for projects
     * whose artwork is hosted elsewhere.
     */
    public static function projectImage(WP_Post $post, string $size = 'large'): string
    {
        if (has_post_thumbnail($post)) {
            return (string) get_the_post_thumbnail_url($post, $size);
        }

        return (string) self::field('image_url', $post->ID, '');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function articles(int $limit = -1): array
    {
        $posts = get_posts([
            'post_type' => 'post',
            'numberposts' => $limit,
            'post_status' => 'publish',
        ]);

        return array_map([self::class, 'article'], $posts);
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(WP_Post $post): array
    {
        $tags = wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']);

        return [
            'id' => $post->post_name,
            'title' => self::text(get_the_title($post)),
            'description' => self::text(get_the_excerpt($post)),
            'content' => apply_filters('the_content', $post->post_content),
            'date' => get_the_date('c', $post),
            'dateFormatted' => get_the_date(get_option('date_format'), $post),
            'readTime' => (string) self::field('read_time', $post->ID, ''),
            'tags' => array_map([self::class, 'text'], is_wp_error($tags) ? [] : $tags),
            'permalink' => get_permalink($post),
            'author' => [
                'name' => get_the_author_meta('display_name', (int) $post->post_author),
                'avatar' => get_avatar_url((int) $post->post_author, ['size' => 160]),
            ],
        ];
    }
}
