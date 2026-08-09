<?php

/**
 * Server-side CV rendering.
 *
 * Replaces the client-side @react-pdf/renderer document from the React app:
 * the same About/Experience content that the page renders is piped through a
 * Blade template into mpdf and streamed from /cv.pdf.
 */

namespace App;

use Mpdf\Mpdf;
use Mpdf\MpdfException;

const CV_QUERY_VAR = 'edgar_cv';

/**
 * Expose /cv.pdf.
 *
 * @return void
 */
add_action('init', function () {
    add_rewrite_rule('^cv\.pdf$', 'index.php?'.CV_QUERY_VAR.'=1', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = CV_QUERY_VAR;

    return $vars;
});

/**
 * The permalink structure ends in a slash, so canonical redirection would
 * bounce /cv.pdf to /cv.pdf/ before the PDF is ever streamed.
 *
 * @return string|false
 */
add_filter('redirect_canonical', function ($redirect) {
    return get_query_var(CV_QUERY_VAR) ? false : $redirect;
});

/**
 * Flush rewrite rules once when the theme is activated so /cv.pdf and the
 * project permalinks resolve without a manual permalink save.
 *
 * @return void
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

/**
 * Build the single contact line printed under the name.
 */
function cv_contact_line(): string
{
    // Email first, then code host, then network — the order the React CV used.
    $order = ['send', 'mail', 'github', 'linkedin'];
    $parts = [];

    foreach (Content::hero()['socialLinks'] as $link) {
        $position = array_search($link['icon'], $order, true);

        if ($position === false) {
            continue;
        }

        $parts[$position][] = preg_replace('#^(mailto:|https?://(www\.)?)#', '', $link['href']);
    }

    ksort($parts);

    return implode(' • ', array_unique(array_merge(...array_values($parts) ?: [[]])));
}

/**
 * Stream the CV.
 *
 * @return void
 */
add_action('template_redirect', function () {
    if (! get_query_var(CV_QUERY_VAR)) {
        return;
    }

    if (! class_exists(Mpdf::class)) {
        wp_die(
            esc_html__('mpdf is not installed. Run "composer install" inside the theme.', 'edgar-ferreira'),
            '',
            ['response' => 500]
        );
    }

    $about = Content::about();
    $work = Content::work();

    $html = view('cv', [
        'about' => $about,
        'work' => $work,
        'name' => $about['name'] ?: get_bloginfo('name', 'display'),
        'role' => __('Product Engineer', 'edgar-ferreira'),
        'contactLine' => cv_contact_line(),
    ])->render();

    $uploads = wp_upload_dir();
    $tempDir = trailingslashit($uploads['basedir']).'mpdf';

    if (! wp_mkdir_p($tempDir)) {
        wp_die(
            esc_html__('Unable to create the mpdf temporary directory.', 'edgar-ferreira'),
            '',
            ['response' => 500]
        );
    }

    try {
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 14,
            'margin_right' => 14,
            'margin_top' => 14,
            'margin_bottom' => 18,
            'margin_footer' => 8,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle(sprintf('%s — CV', $about['name'] ?: get_bloginfo('name', 'display')));
        $mpdf->SetAuthor($about['name'] ?: get_bloginfo('name', 'display'));
        $mpdf->SetHTMLFooter(sprintf(
            '<div style="border-top:1px solid #E5E5E5;padding-top:6pt;text-align:center;font-size:8pt;color:#AAAAAA;">%s • %s</div>',
            esc_html(sprintf(
                /* translators: %s is the site host */
                __('Generated from %s', 'edgar-ferreira'),
                wp_parse_url(home_url(), PHP_URL_HOST)
            )),
            esc_html(wp_date('Y'))
        ));

        $mpdf->WriteHTML($html);

        $filename = sprintf(
            '%s_CV.pdf',
            str_replace('-', '_', sanitize_title($about['name'] ?: get_bloginfo('name')))
        );

        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    } catch (MpdfException $exception) {
        wp_die(
            esc_html(sprintf(
                /* translators: %s is the underlying error message */
                __('Could not render the CV: %s', 'edgar-ferreira'),
                $exception->getMessage()
            )),
            '',
            ['response' => 500]
        );
    }

    exit;
});
