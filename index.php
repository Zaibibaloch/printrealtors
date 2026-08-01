<?php

/**
 * Hostinger document root is public_html (not public/).
 * Boot the Laravel front controller directly — never redirect to /public
 * (that causes a redirect loop with the rewrite rules below).
 */
require __DIR__ . '/public/index.php';
