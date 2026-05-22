<?php

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * HOSTINGER PRODUCTION INDEX.PHP
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * This file goes in ~/public_html/ on your Hostinger server.
 * It points to the Laravel project located OUTSIDE public_html.
 *
 * SETUP:
 * 1. Put your Laravel project at: ~/webworkinvoice/
 * 2. Copy this file to: ~/public_html/index.php
 * 3. Copy public/.htaccess to: ~/public_html/.htaccess
 * 4. Copy public/build/ to: ~/public_html/build/
 * 5. Copy public/favicon.ico to: ~/public_html/favicon.ico
 * 6. Create symlink: ln -s ~/webworkinvoice/storage/app/public ~/public_html/storage
 *
 * IMPORTANT: Update $laravelBasePath below if your project is in a different folder.
 * ─────────────────────────────────────────────────────────────────────────────
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ── Path to your Laravel project (one level above public_html) ──────────────
// Adjust this path to match your Hostinger setup.
// Common Hostinger paths:
//   /home/username/webworkinvoice
//   /home/username/domains/yourdomain.com/webworkinvoice
$laravelBasePath = dirname(__DIR__) . '/webworkinvoice';

// ── Maintenance mode ────────────────────────────────────────────────────────
if (file_exists($maintenance = $laravelBasePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// ── Register the Composer autoloader ────────────────────────────────────────
require $laravelBasePath . '/vendor/autoload.php';

// ── Bootstrap Laravel and handle the request ────────────────────────────────
$app = require_once $laravelBasePath . '/bootstrap/app.php';

// Tell Laravel where the "public" path is (public_html, not project/public)
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
