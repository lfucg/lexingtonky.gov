<?php

if (php_sapi_name() != "cli") {
  $redirects = __DIR__ . "/redirects.php";
  if (file_exists($redirects)) {
    include_once $redirects;
  }
}
/**
 * Insure timezone is set correctly.
 */
date_default_timezone_set('America/New_York');

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

$settings['config_sync_directory'] = $app_root . '/config/sync';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include_once __DIR__ . "/settings.pantheon.php";

$settings['trusted_host_patterns'] = array(
  '^.+lexky-d8\.pantheonsite\.io$',
  '^www\.lexingtonky\.gov$',
  '^lexky-d8\.lndo\.site$', // windows & Mac
  '^web$',
  '^localhost$',
  '^www\.covid19renterhelp\.org$',
  '^covid19renterhelp\.org$'
);

$config_overrides = __DIR__ . "/config.overrides.php";
if (file_exists($config_overrides)) {
  include_once $config_overrides;
}

if (! (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live')) {
  $config['system.mail']['interface']['default'] = 'test_mail_collector';
}

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include_once $local_settings;
}
$settings['install_profile'] = 'standard';

/**
 * Define appropriate location for tmp directory
 *
 * Issue: https://github.com/pantheon-systems/drops-8/issues/114
 *
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] !== 'kalabox') {
  $config['system.file']['path']['temporary'] = $_SERVER['HOME'] .'/tmp';
}

/*Add to settings.php*/

// Relocate the compiled twig files to <binding-dir>/tmp/twig. This will improve
// performance, but may cause problems in the live environment when multiple
// app servers are in use. This is solved with the directives below.
$settings['php_storage']['twig']['directory'] = $_SERVER['HOME'] . '/tmp';
// Increase the deployment identifier sequence number every time code
// is deployed to the live environment if the deployment contains changes
// to any twig templates. FUTURE: Pantheon will provide a deployment environment
// via an environment variable.
$settings['deployment_identifier'] = '1';
// Ensure that the compiled twig templates will be rebuilt whenever the
// deployment identifier changes. Note that a cache rebuild is also necessary
// (although insufficient, without this setting), as the twig-generated content
// itself is also cached in the database. Without this setting, deploying
// new twig source files to a live environment with multiple app servers will
// result in some (most) of the app servers continuing to serve the old, stale
// compiled template files.

// Hash salt is unidentified.
// $settings['php_storage']['twig']['secret'] = $settings['hash_salt'] . $settings['deployment_identifier'];

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include_once $app_root . '/' . $site_path . '/settings.local.php';
}

if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  if ($_ENV['PANTHEON_ENVIRONMENT'] == 'lando') {
    // print('local');
    // Enable lando server and set index to use it for local development.
    $config['search_api.server.lando']['status'] = true;
    $config['search_api.index.pantheon_index']['server'] = 'lando';
  } else {
    // Pantheon Configuration.
    $config['search_api.server.pantheon']['status'] = true;
    $config['search_api.index.pantheon_index']['server'] = 'pantheon';
  }
}
