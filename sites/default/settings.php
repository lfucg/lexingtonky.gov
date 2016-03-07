<?php

// Require HTTPS on pantheon
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) &&
  $_SERVER['HTTPS'] === 'OFF') {
  if (!isset($_SERVER['HTTP_X_SSL']) ||
    (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] != 'ON')) {
    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    exit();
  }
}

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

$settings['trusted_host_patterns'] = array(
  '^dev-lexky-d8\.pantheonsite\.io$',
  '^test-lexky-d8\.pantheonsite\.io$',
  '^live-lexky-d8\.pantheonsite\.io$',
  '^.+lexky-d8\.pantheonsite\.io$',
  '^next\.lexingtonky\.gov$',
);

if (! (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live')) {
  $config['core.extension']['module']['devel'] = 0;
  $config['system.mail']['interface']['default'] = 'devel_mail_log';
}

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
$settings['install_profile'] = 'standard';

