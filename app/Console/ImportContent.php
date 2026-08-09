<?php

namespace App\Console;

use WP_CLI;
use WP_Query;

/**
 * Imports the markdown content that shipped with the React app into
 * WordPress: options-page fields, the Projects post type and article posts.
 *
 * Idempotent — projects and articles are matched on slug and attachments on
 * their source URL, so re-running updates in place rather than duplicating.
 */
class ImportContent
{
    protected string $contentDir;

    protected string $publicDir;

    protected int $authorId = 0;

    /**
     * Import portfolio content from the original React app.
     *
     * ## OPTIONS
     *
     * [--path=<path>]
     * : Path to the React app. Defaults to base/ in the project root.
     *
     * [--locale=<locale>]
     * : Which content locale to import. Default: en
     *
     * [--skip-media]
     * : Skip downloading images. Projects keep their external image URLs.
     *
     * [--keep-samples]
     * : Keep WordPress's default "Hello world!" post and "Sample Page".
     *   They are moved to the trash by default so the articles grid matches
     *   the imported content.
     *
     * ## EXAMPLES
     *
     *     wp portfolio import
     *     wp portfolio import --path=/var/www/html/base --skip-media
     *
     * @when after_wp_load
     */
    public function __invoke(array $args, array $assoc): void
    {
        $base = rtrim($assoc['path'] ?? \dirname(ABSPATH, 2).'/base', '/');
        $locale = $assoc['locale'] ?? 'en';
        $skipMedia = isset($assoc['skip-media']);

        $this->contentDir = "{$base}/content/{$locale}";
        $this->publicDir = "{$base}/public";

        if (! is_dir($this->contentDir)) {
            WP_CLI::error("Content directory not found: {$this->contentDir}");
        }

        if (! function_exists('update_field')) {
            WP_CLI::error('Secure Custom Fields is not active — activate it before importing.');
        }

        // WP-CLI runs with no current user, so posts would otherwise be
        // created with post_author = 0 and render without a byline.
        $this->authorId = $this->resolveAuthor();

        WP_CLI::log("Importing from {$this->contentDir}");

        $this->importPages($skipMedia);
        $this->importProjects($skipMedia);
        $this->importArticles();

        if (! isset($assoc['keep-samples'])) {
            $this->trashSampleContent();
        }

        $this->configureSite();

        WP_CLI::success('Content imported.');
    }

    /**
     * Read and parse one markdown file.
     *
     * @return array{frontmatter: array, body: string}|null
     */
    protected function read(string $relativePath): ?array
    {
        $path = "{$this->contentDir}/{$relativePath}";

        if (! is_readable($path)) {
            WP_CLI::warning("Missing file: {$path}");

            return null;
        }

        return Markdown::parse((string) file_get_contents($path));
    }

    /**
     * Wrap a list of strings into repeater rows.
     */
    protected function rows(array $values): array
    {
        return array_map(fn ($value) => ['value' => $value], array_values($values));
    }

    /**
     * Front-page section content -> options page fields.
     */
    protected function importPages(bool $skipMedia): void
    {
        $hero = $this->read('pages/hero.md');

        if ($hero) {
            $data = $hero['frontmatter'];

            update_field('hero_title_left', $data['titleLeft'] ?? '', 'option');
            update_field('hero_title_right', $data['titleRight'] ?? '', 'option');
            update_field('hero_description', $data['description'] ?? '', 'option');
            update_field('hero_cta_label', $data['ctaLabel'] ?? '', 'option');
            update_field('hero_cta_href', $data['ctaHref'] ?? '#projects', 'option');
            update_field('hero_carousel_label', $data['carouselLabel'] ?? '', 'option');
            update_field('hero_social_links', $data['socialLinks'] ?? [], 'option');

            WP_CLI::log('  hero');
        }

        $about = $this->read('pages/about.md');

        if ($about) {
            $data = $about['frontmatter'];

            update_field('about_title', $data['title'] ?? '', 'option');
            update_field('about_greeting', $data['greeting'] ?? '', 'option');
            update_field('about_name', $data['name'] ?? '', 'option');
            update_field('about_role_summary', $data['roleSummary'] ?? '', 'option');
            update_field('about_experience_years', $data['experienceYears'] ?? '', 'option');
            update_field('about_experience_text', $data['experienceText'] ?? '', 'option');
            update_field('about_principles_text', $data['principlesText'] ?? '', 'option');
            update_field('about_skills', $this->rows($data['skills'] ?? []), 'option');
            update_field('about_what_i_do', $this->rows($data['whatIDo'] ?? []), 'option');
            update_field('about_education_heading', $data['educationHeading'] ?? '', 'option');
            update_field('about_education', $this->rows($data['education'] ?? []), 'option');
            update_field('about_certifications_heading', $data['certificationsHeading'] ?? '', 'option');
            update_field('about_certifications', $this->rows($data['certifications'] ?? []), 'option');

            if (! $skipMedia && ! empty($data['image'])) {
                $attachmentId = $this->attachLocal($data['image']);

                if ($attachmentId) {
                    update_field('about_image', $attachmentId, 'option');
                }
            }

            WP_CLI::log('  about');
        }

        $work = $this->read('pages/work.md');

        if ($work) {
            $data = $work['frontmatter'];

            update_field('work_heading', $data['heading'] ?? '', 'option');
            update_field('work_experiences', $data['experiences'] ?? [], 'option');
            update_field('work_summary_label', $data['summaryLabel'] ?? '', 'option');
            update_field('work_summary_value', $data['summaryValue'] ?? '', 'option');

            WP_CLI::log('  work');
        }

        $contact = $this->read('pages/contact.md');

        if ($contact) {
            $data = $contact['frontmatter'];

            update_field('contact_title', $data['title'] ?? '', 'option');
            update_field('contact_name', $data['name'] ?? '', 'option');
            update_field('contact_title_suffix', $data['titleSuffix'] ?? '', 'option');
            update_field('contact_info', $data['contactInfo'] ?? [], 'option');
            update_field('contact_social_links', $data['socialLinks'] ?? [], 'option');
            update_field('contact_footer', $data['footer'] ?? '', 'option');

            WP_CLI::log('  contact');
        }

        $projects = $this->read('pages/projects.md');

        if ($projects) {
            $data = $projects['frontmatter'];

            update_field('projects_title', $data['title'] ?? '', 'option');
            update_field('projects_cta_label', $data['ctaLabel'] ?? 'GitHub', 'option');
            update_field('projects_cta_href', $data['ctaHref'] ?? '#', 'option');

            WP_CLI::log('  projects section');
        }

        $articles = $this->read('pages/articles.md');

        if ($articles) {
            update_field('articles_heading', $articles['frontmatter']['heading'] ?? '', 'option');

            WP_CLI::log('  articles section');
        }
    }

    /**
     * content/{locale}/projects/*.md -> the Projects post type.
     */
    protected function importProjects(bool $skipMedia): void
    {
        $files = glob("{$this->contentDir}/projects/*.md") ?: [];
        $created = 0;
        $updated = 0;

        foreach ($files as $file) {
            ['frontmatter' => $data, 'body' => $body] = Markdown::parse((string) file_get_contents($file));

            if (empty($data['id'])) {
                WP_CLI::warning('Skipping project without an id: '.basename($file));

                continue;
            }

            $existing = $this->findBySlug($data['id'], 'project');

            $postarr = [
                'post_type' => 'project',
                'post_name' => $data['id'],
                'post_title' => $data['title'] ?? $data['id'],
                'post_content' => Markdown::toHtml($body),
                'post_excerpt' => $data['description'] ?? '',
                'post_status' => 'publish',
                'post_author' => $this->authorId,
                'menu_order' => (int) ($data['order'] ?? 0),
            ];

            if ($existing) {
                $postarr['ID'] = $existing;
                $postId = wp_update_post($postarr, true);
                $updated++;
            } else {
                $postId = wp_insert_post($postarr, true);
                $created++;
            }

            if (is_wp_error($postId)) {
                WP_CLI::warning("Failed to save project {$data['id']}: ".$postId->get_error_message());

                continue;
            }

            update_field('description', $data['description'] ?? '', $postId);
            update_field('demo_url', $data['demoUrl'] ?? '', $postId);
            update_field('github_url', $data['githubUrl'] ?? '', $postId);
            update_field('features', $this->rows($data['features'] ?? []), $postId);
            update_field('technologies', $this->rows($data['technologies'] ?? []), $postId);

            if (! empty($data['tags'])) {
                wp_set_object_terms($postId, $data['tags'], 'project_tag');
                // Taxonomy terms come back sorted by name; the design shows
                // them as authored, so keep the original sequence alongside.
                update_post_meta($postId, '_project_tag_order', $data['tags']);
            }

            $this->setProjectImage($postId, $data['image'] ?? '', $skipMedia);
        }

        WP_CLI::log(sprintf('  projects: %d created, %d updated', $created, $updated));
    }

    /**
     * Attach the project artwork, falling back to storing the remote URL.
     */
    protected function setProjectImage(int $postId, string $image, bool $skipMedia): void
    {
        if (! $image) {
            return;
        }

        if ($skipMedia || ! str_starts_with($image, 'http')) {
            $attachmentId = str_starts_with($image, 'http') ? 0 : $this->attachLocal($image);

            if ($attachmentId) {
                set_post_thumbnail($postId, $attachmentId);

                return;
            }

            update_field('image_url', $image, $postId);

            return;
        }

        $attachmentId = $this->attachRemote($image, $postId);

        if ($attachmentId) {
            set_post_thumbnail($postId, $attachmentId);
            update_field('image_url', '', $postId);

            return;
        }

        // Sideloading can fail offline or behind a proxy; the template falls
        // back to this URL so the layout still renders.
        update_field('image_url', $image, $postId);
        WP_CLI::warning("Could not sideload {$image} — kept as an external URL.");
    }

    /**
     * content/{locale}/articles/*.md -> posts.
     */
    protected function importArticles(): void
    {
        $files = glob("{$this->contentDir}/articles/*.md") ?: [];
        $created = 0;
        $updated = 0;

        foreach ($files as $file) {
            ['frontmatter' => $data, 'body' => $body] = Markdown::parse((string) file_get_contents($file));

            if (empty($data['id'])) {
                WP_CLI::warning('Skipping article without an id: '.basename($file));

                continue;
            }

            $existing = $this->findBySlug($data['id'], 'post');

            $postarr = [
                'post_type' => 'post',
                'post_name' => $data['id'],
                'post_title' => $data['title'] ?? $data['id'],
                'post_content' => Markdown::toHtml($body),
                'post_excerpt' => $data['description'] ?? '',
                'post_status' => 'publish',
                'post_author' => $this->authorId,
            ];

            if (! empty($data['date'])) {
                $postarr['post_date'] = gmdate('Y-m-d H:i:s', strtotime($data['date']));
                $postarr['post_date_gmt'] = $postarr['post_date'];
            }

            if ($existing) {
                $postarr['ID'] = $existing;
                $postId = wp_update_post($postarr, true);
                $updated++;
            } else {
                $postId = wp_insert_post($postarr, true);
                $created++;
            }

            if (is_wp_error($postId)) {
                WP_CLI::warning("Failed to save article {$data['id']}: ".$postId->get_error_message());

                continue;
            }

            update_field('read_time', $data['readTime'] ?? '', $postId);

            if (! empty($data['tags'])) {
                wp_set_object_terms($postId, $data['tags'], 'post_tag');
            }
        }

        WP_CLI::log(sprintf('  articles: %d created, %d updated', $created, $updated));
    }

    /**
     * Move WordPress's default sample content to the trash.
     *
     * "Hello world!" would otherwise appear as an extra card in the articles
     * grid. Trashed rather than deleted, so it stays recoverable.
     */
    protected function trashSampleContent(): void
    {
        foreach (['hello-world' => 'post', 'sample-page' => 'page'] as $slug => $type) {
            $id = $this->findBySlug($slug, $type);

            if (! $id || get_post_status($id) === 'trash') {
                continue;
            }

            wp_trash_post($id);
            WP_CLI::log("  trashed sample content: {$slug}");
        }
    }

    /**
     * Front page, permalinks and tagline.
     */
    protected function configureSite(): void
    {
        $front = get_page_by_path('home');

        if (! $front) {
            $frontId = wp_insert_post([
                'post_type' => 'page',
                'post_title' => __('Home', 'edgar-ferreira'),
                'post_name' => 'home',
                'post_status' => 'publish',
            ]);
        } else {
            $frontId = $front->ID;
        }

        if (! is_wp_error($frontId)) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $frontId);
        }

        // /article/{slug} and /project/{slug}, matching the React routes.
        if (get_option('permalink_structure') !== '/article/%postname%/') {
            update_option('permalink_structure', '/article/%postname%/');
        }

        flush_rewrite_rules();

        $this->syncAuthorName();

        WP_CLI::log('  site: front page, permalinks, rewrite rules');
    }

    /**
     * Pick the user imported content is attributed to: the first
     * administrator, falling back to any existing user.
     */
    protected function resolveAuthor(): int
    {
        $admins = get_users([
            'role' => 'administrator',
            'number' => 1,
            'fields' => 'ID',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        if ($admins) {
            return (int) $admins[0];
        }

        $any = get_users(['number' => 1, 'fields' => 'ID']);

        return $any ? (int) $any[0] : 0;
    }

    /**
     * Align the author's display name with the name in the About content, so
     * article bylines read "Edgar Ferreira" rather than the login.
     */
    protected function syncAuthorName(): void
    {
        $name = (string) \App\Content::option('about_name', '');

        if (! $name || ! $this->authorId) {
            return;
        }

        $user = get_userdata($this->authorId);

        if (! $user || $user->display_name === $name) {
            return;
        }

        wp_update_user(['ID' => $this->authorId, 'display_name' => $name]);
        WP_CLI::log("  author: display name set to \"{$name}\"");
    }

    /**
     * Find an existing post of a type by slug.
     */
    protected function findBySlug(string $slug, string $postType): ?int
    {
        $query = new WP_Query([
            'name' => $slug,
            'post_type' => $postType,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        return $query->posts ? (int) $query->posts[0] : null;
    }

    /**
     * Import a file from the React app's public/ directory.
     */
    protected function attachLocal(string $relativePath): ?int
    {
        $source = $this->publicDir.'/'.ltrim($relativePath, '/');

        if (! is_readable($source)) {
            WP_CLI::warning("Image not found: {$source}");

            return null;
        }

        if ($existing = $this->findAttachment($relativePath)) {
            return $existing;
        }

        $this->loadMediaFunctions();

        $temp = wp_tempnam(basename($source));
        copy($source, $temp);

        $attachmentId = media_handle_sideload([
            'name' => basename($source),
            'tmp_name' => $temp,
        ], 0);

        if (is_wp_error($attachmentId)) {
            @unlink($temp);
            WP_CLI::warning("Could not import {$source}: ".$attachmentId->get_error_message());

            return null;
        }

        update_post_meta($attachmentId, '_portfolio_source', $relativePath);

        return (int) $attachmentId;
    }

    /**
     * Download a remote image into the media library.
     *
     * media_sideload_image() is not used here because it requires a file
     * extension in the URL, which the Unsplash links in the source content do
     * not have. The extension is derived from the downloaded file instead.
     */
    protected function attachRemote(string $url, int $postId): ?int
    {
        if ($existing = $this->findAttachment($url)) {
            return $existing;
        }

        $this->loadMediaFunctions();

        $temp = download_url($url);

        if (is_wp_error($temp)) {
            WP_CLI::warning("Download failed for {$url}: ".$temp->get_error_message());

            return null;
        }

        $mime = wp_get_image_mime($temp);
        $extension = $mime ? array_search($mime, wp_get_mime_types(), true) : false;

        if (! $extension) {
            @unlink($temp);
            WP_CLI::warning("Unrecognised image type for {$url}");

            return null;
        }

        // wp_get_mime_types() keys look like "jpg|jpeg|jpe"; take the first.
        $extension = explode('|', $extension)[0];
        $name = sanitize_file_name(basename((string) wp_parse_url($url, PHP_URL_PATH)).'.'.$extension);

        $attachmentId = media_handle_sideload([
            'name' => $name,
            'tmp_name' => $temp,
        ], $postId);

        if (is_wp_error($attachmentId)) {
            @unlink($temp);
            WP_CLI::warning("Sideload failed for {$url}: ".$attachmentId->get_error_message());

            return null;
        }

        update_post_meta($attachmentId, '_portfolio_source', $url);

        return (int) $attachmentId;
    }

    /**
     * Look up a previously imported attachment by its source.
     */
    protected function findAttachment(string $source): ?int
    {
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'fields' => 'ids',
            'meta_key' => '_portfolio_source',
            'meta_value' => $source,
        ]);

        return $existing ? (int) $existing[0] : null;
    }

    protected function loadMediaFunctions(): void
    {
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
    }
}
