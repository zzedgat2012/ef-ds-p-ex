<?php

namespace App\Console;

/**
 * Minimal markdown reader for the content that shipped with the React app.
 *
 * The source files carry JSON front matter between `---` fences (not YAML),
 * which is what base/src/lib/markdown.ts parsed. The body converter covers the
 * subset of markdown those files actually use — the same subset the React
 * ArticleDetail component hand-rendered.
 */
class Markdown
{
    /**
     * Split a file into its JSON front matter and body.
     *
     * @return array{frontmatter: array, body: string}
     */
    public static function parse(string $raw): array
    {
        $raw = str_replace("\r\n", "\n", $raw);

        if (! preg_match('/^---\s*\n([\s\S]*?)\n---\s*\n?/', $raw, $matches)) {
            return ['frontmatter' => [], 'body' => trim($raw)];
        }

        $frontmatter = json_decode($matches[1], true);

        return [
            'frontmatter' => \is_array($frontmatter) ? $frontmatter : [],
            'body' => trim(substr($raw, \strlen($matches[0]))),
        ];
    }

    /**
     * Convert a markdown body to semantic HTML.
     *
     * Output is deliberately class-free: the article template styles it with
     * the typography plugin, and app.js decorates code blocks at runtime. That
     * keeps the stored content editable in the block editor.
     */
    public static function toHtml(string $markdown): string
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $markdown));
        $html = [];

        $inCode = false;
        $codeLanguage = '';
        $codeLines = [];
        $listItems = [];

        $flushList = function () use (&$listItems, &$html) {
            if (! $listItems) {
                return;
            }

            $html[] = '<ul>'.implode('', array_map(
                fn ($item) => '<li>'.self::inline($item).'</li>',
                $listItems
            )).'</ul>';

            $listItems = [];
        };

        foreach ($lines as $line) {
            if (str_starts_with($line, '```')) {
                if (! $inCode) {
                    $flushList();
                    $inCode = true;
                    $codeLanguage = trim(substr($line, 3)) ?: 'plaintext';
                    $codeLines = [];

                    continue;
                }

                $inCode = false;
                $html[] = sprintf(
                    '<pre><code class="language-%s">%s</code></pre>',
                    esc_attr($codeLanguage),
                    esc_html(implode("\n", $codeLines))
                );

                continue;
            }

            if ($inCode) {
                $codeLines[] = $line;

                continue;
            }

            if (str_starts_with($line, '### ')) {
                $flushList();
                $html[] = '<h3>'.self::inline(substr($line, 4)).'</h3>';
            } elseif (str_starts_with($line, '## ')) {
                $flushList();
                $html[] = '<h2>'.self::inline(substr($line, 3)).'</h2>';
            } elseif (str_starts_with($line, '# ')) {
                $flushList();
                $html[] = '<h2>'.self::inline(substr($line, 2)).'</h2>';
            } elseif (str_starts_with($line, '> ')) {
                $flushList();
                $html[] = '<blockquote><p>'.self::inline(substr($line, 2)).'</p></blockquote>';
            } elseif (str_starts_with($line, '- ')) {
                $listItems[] = substr($line, 2);
            } elseif (trim($line) === '') {
                $flushList();
            } else {
                $flushList();
                $html[] = '<p>'.self::inline($line).'</p>';
            }
        }

        $flushList();

        // An unterminated fence would otherwise swallow the tail of the file.
        if ($inCode && $codeLines) {
            $html[] = sprintf(
                '<pre><code class="language-%s">%s</code></pre>',
                esc_attr($codeLanguage),
                esc_html(implode("\n", $codeLines))
            );
        }

        return implode("\n", $html);
    }

    /**
     * Inline markdown: bold, inline code and links.
     */
    protected static function inline(string $text): string
    {
        $text = esc_html($text);

        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);

        return preg_replace(
            '/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/',
            '<a href="$2">$1</a>',
            $text
        );
    }
}
