<?php

/**
 * WP-CLI commands.
 */

namespace App;

use App\Console\ImportContent;

if (\defined('WP_CLI') && \WP_CLI) {
    \WP_CLI::add_command('portfolio import', ImportContent::class);
}
