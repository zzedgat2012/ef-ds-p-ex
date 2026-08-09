<?php

/**
 * Machine-readable discovery surfaces.
 *
 *   /wp-sitemap.xml           XML sitemap (WordPress core, tuned below)
 *   /llms.txt                 llmstxt.org summary for language models
 *   /.well-known/agents.json  Web2Agent (W2A) 1.0 manifest
 *
 * The last two are generated from the same content the templates render, so
 * they never drift from what is in the CMS.
 *
 * @link https://llmstxt.org/
 * @link https://w2a-protocol.org/
 */

namespace App;

const DISCOVERY_QUERY_VAR = 'edgar_discovery';

/*
|--------------------------------------------------------------------------
| XML sitemap
|--------------------------------------------------------------------------
|
| Core generates /wp-sitemap.xml for every public post type, which already
| covers pages, articles and the Projects post type. Note that WordPress
| disables sitemaps entirely when blog_public is 0 — Bedrock sets that in the
| development environment, so this only serves in staging and production.
*/

/**
 * Drop the users sitemap.
 *
 * A single-author portfolio gains nothing from it, and it publishes an author
 * enumeration endpoint for free.
 *
 * @return \WP_Sitemaps_Provider|false
 */
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

/**
 * Return a real 404 for sitemap URLs with no provider behind them.
 *
 * Unregistering a provider removes it from the index but leaves its rewrite
 * rule in place: core's renderer simply returns, WordPress carries on with
 * template loading, and /wp-sitemap-users-1.xml answers 200 with an HTML page.
 * Runs after core's renderer (priority 10), which exits for valid sitemaps.
 *
 * @return void
 */
add_action('template_redirect', function () {
    $sitemap = get_query_var('sitemap');

    if (! $sitemap || wp_sitemaps_get_server()->registry->get_provider($sitemap)) {
        return;
    }

    global $wp_query;

    $wp_query->set_404();
    status_header(404);
    nocache_headers();
}, 11);

/**
 * Tell crawlers when each entry actually changed.
 *
 * @return array
 */
add_filter('wp_sitemaps_posts_entry', function ($entry, $post) {
    $entry['lastmod'] = (string) get_post_modified_time('c', true, $post);

    return $entry;
}, 10, 2);

/*
 * Note: core already skips the page assigned to page_on_front in the pages
 * sitemap and emits home_url('/') in its place, so no de-duplication filter is
 * needed here. Excluding it again removes the pages provider entirely — and
 * with it the homepage URL — when Home is the only published page.
 */

/*
|--------------------------------------------------------------------------
| llms.txt and agents.json
|--------------------------------------------------------------------------
*/

/**
 * @return void
 */
add_action('init', function () {
    add_rewrite_rule('^llms\.txt$', 'index.php?'.DISCOVERY_QUERY_VAR.'=llms', 'top');
    add_rewrite_rule('^\.well-known/agents\.json$', 'index.php?'.DISCOVERY_QUERY_VAR.'=agents', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = DISCOVERY_QUERY_VAR;

    return $vars;
});

/**
 * Both paths carry a file extension, so canonical redirection would otherwise
 * bounce them to a trailing slash.
 *
 * @return string|false
 */
add_filter('redirect_canonical', function ($redirect) {
    return get_query_var(DISCOVERY_QUERY_VAR) ? false : $redirect;
});

/**
 * @return void
 */
add_action('template_redirect', function () {
    $target = get_query_var(DISCOVERY_QUERY_VAR);

    if (! $target) {
        return;
    }

    if ($target === 'llms') {
        // Deliberately no X-Robots-Tag: the file exists to be fetched and used
        // by crawlers, so telling them to ignore it defeats the purpose.
        header('Content-Type: text/plain; charset=utf-8');
        echo llms_txt();
        exit;
    }

    if ($target === 'agents') {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo wp_json_encode(agents_manifest(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
});

/**
 * Collapse a string onto one line — llms.txt entries are one per line, and
 * JSON descriptions read better without stray newlines.
 */
function one_line(string $text, int $limit = 200): string
{
    $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($text)));

    return mb_strlen($text) > $limit
        ? rtrim(mb_substr($text, 0, $limit - 1)).'…'
        : $text;
}

/**
 * Build /llms.txt.
 *
 * Structure follows the llmstxt.org spec: one H1, a blockquote summary, then
 * H2 sections of "- [title](url): description" links, with lower-priority
 * material under "## Optional".
 */
function llms_txt(): string
{
    $hero = Content::hero();
    $about = Content::about();
    $contact = Content::contact();

    $name = $about['name'] ?: get_bloginfo('name', 'display');
    $lines = [];

    $lines[] = '# '.$name;
    $lines[] = '';
    $lines[] = '> '.one_line(sprintf(
        '%s %s. %s',
        $hero['titleLeft'],
        $hero['titleRight'],
        $hero['description']
    ), 400);
    $lines[] = '';

    if ($about['roleSummary']) {
        $lines[] = one_line($about['greeting'].' '.$name.', '.$about['roleSummary'], 400);
        $lines[] = '';
    }

    if ($about['skills']) {
        $lines[] = 'Core skills: '.implode(', ', $about['skills']).'.';
        $lines[] = '';
    }

    $projects = Content::projects();

    if ($projects) {
        $lines[] = '## Projects';
        $lines[] = '';

        foreach ($projects as $project) {
            $lines[] = sprintf(
                '- [%s](%s): %s',
                $project['title'],
                $project['permalink'],
                one_line($project['description'])
            );
        }

        $lines[] = '';
    }

    $articles = Content::articles();

    if ($articles) {
        $lines[] = '## Articles';
        $lines[] = '';

        foreach ($articles as $article) {
            $lines[] = sprintf(
                '- [%s](%s): %s',
                $article['title'],
                $article['permalink'],
                one_line($article['description'])
            );
        }

        $lines[] = '';
    }

    $lines[] = '## Resources';
    $lines[] = '';
    $lines[] = sprintf('- [Curriculum vitae](%s): full CV as a PDF, generated from this site.', home_url('/cv.pdf'));
    $lines[] = sprintf('- [REST API](%s): JSON for every project, article and tag.', home_url('/wp-json/wp/v2'));
    $lines[] = sprintf('- [Agent manifest](%s): Web2Agent (W2A) capability declaration.', home_url('/.well-known/agents.json'));
    $lines[] = '';

    $lines[] = '## Optional';
    $lines[] = '';
    $lines[] = sprintf('- [Sitemap](%s): every indexable URL.', home_url('/wp-sitemap.xml'));

    foreach ($contact['contactInfo'] as $info) {
        if (! empty($info['value']) && str_contains(strtolower($info['label']), 'email')) {
            $lines[] = sprintf('- Contact (%s): %s', strtolower($info['label']), $info['value']);
        }
    }

    return implode("\n", $lines)."\n";
}

/**
 * Build the /.well-known/agents.json manifest.
 *
 * Skills point at the WordPress REST API, which already serves the Projects
 * post type and articles as JSON, so agents get real endpoints rather than a
 * description of pages they would have to scrape.
 *
 * @return array<string, mixed>
 */
function agents_manifest(): array
{
    $about = Content::about();
    $hero = Content::hero();

    $name = $about['name'] ?: get_bloginfo('name', 'display');
    $description = one_line($hero['description'] ?: get_bloginfo('description'), 300);

    // The spec requires https for a2a_profile.url. Forcing the scheme keeps the
    // manifest valid even when it is generated over plain http locally.
    $manifestUrl = set_url_scheme(home_url('/.well-known/agents.json'), 'https');

    return [
        'w2a' => '1.0',
        'site' => [
            // site.type is a closed enum (ecommerce, blog, saas, marketplace,
            // media, directory, api, other). A portfolio that publishes both
            // projects and articles fits none of them, and labelling it "blog"
            // would tell agents to treat projects as posts.
            'name' => $name,
            'type' => 'other',
            'language' => explode('-', get_bloginfo('language'))[0] ?: 'en',
            'description' => $description,
        ],
        'skills' => [
            [
                'id' => 'list_projects',
                'intent' => 'list the engineering projects in this portfolio',
                'action' => 'GET /wp-json/wp/v2/project',
                'input' => [
                    'per_page' => 'int?',
                    'page' => 'int?',
                    'search' => 'string?',
                ],
                'output' => [
                    'items' => 'Project[]',
                ],
                'auth' => 'none',
                'description' => 'Returns published projects ordered by menu_order. Each item carries title, slug, link, content and featured media.',
            ],
            [
                'id' => 'get_project',
                'intent' => 'fetch a single project by its slug',
                'action' => 'GET /wp-json/wp/v2/project?slug={slug}',
                'input' => [
                    'slug' => 'string',
                ],
                'output' => [
                    'items' => 'Project[]',
                ],
                'auth' => 'none',
                'description' => 'Returns a single-element array for the matching project, or an empty array when the slug is unknown.',
            ],
            [
                'id' => 'list_articles',
                'intent' => 'list the technical articles published on this site',
                'action' => 'GET /wp-json/wp/v2/posts',
                'input' => [
                    'per_page' => 'int?',
                    'page' => 'int?',
                    'search' => 'string?',
                ],
                'output' => [
                    'items' => 'Post[]',
                ],
                'auth' => 'none',
                'description' => 'Returns published articles newest first, with excerpt, rendered content and tags.',
            ],
            [
                'id' => 'get_article',
                'intent' => 'fetch a single article by its slug',
                'action' => 'GET /wp-json/wp/v2/posts?slug={slug}',
                'input' => [
                    'slug' => 'string',
                ],
                'output' => [
                    'items' => 'Post[]',
                ],
                'auth' => 'none',
                'description' => 'Returns a single-element array for the matching article, or an empty array when the slug is unknown.',
            ],
            [
                'id' => 'search_site',
                'intent' => 'search across every project and article on the site',
                'action' => 'GET /wp-json/wp/v2/search?search={q}',
                'input' => [
                    'q' => 'string',
                    'per_page' => 'int?',
                ],
                'output' => [
                    'items' => 'SearchResult[]',
                ],
                'auth' => 'none',
                'description' => 'Full-text search returning id, title, url and subtype for each hit.',
            ],
            [
                'id' => 'download_cv',
                'intent' => 'download the curriculum vitae as a PDF',
                'action' => 'GET /cv.pdf',
                'input' => new \stdClass,
                'output' => [
                    'file' => 'string',
                ],
                'auth' => 'none',
                'description' => 'Server-rendered PDF (application/pdf) covering summary, skills, experience, education and certifications.',
            ],
        ],
        'policies' => [
            'rate_limit' => '60/min',
            'allowed_agents' => ['*'],
            'require_identity' => false,
        ],
        'a2a_profile' => [
            'name' => $name,
            'description' => $description,
            'url' => $manifestUrl,
            'version' => '1.0',
            'provider' => [
                'organization' => $name,
                'url' => set_url_scheme(home_url('/'), 'https'),
            ],
            'capabilities' => [
                'streaming' => false,
                'pushNotifications' => false,
            ],
            'defaultInputModes' => ['application/json'],
            'defaultOutputModes' => ['application/json'],
            'skills' => [
                [
                    'id' => 'list_projects',
                    'name' => 'List projects',
                    'description' => 'List the engineering projects in this portfolio',
                    'tags' => ['portfolio', 'projects'],
                ],
                [
                    'id' => 'list_articles',
                    'name' => 'List articles',
                    'description' => 'List the technical articles published on this site',
                    'tags' => ['portfolio', 'articles', 'blog'],
                ],
                [
                    'id' => 'search_site',
                    'name' => 'Search the site',
                    'description' => 'Search across every project and article',
                    'tags' => ['search'],
                ],
            ],
        ],
    ];
}
