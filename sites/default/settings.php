<?php

if (php_sapi_name() != "cli") {
  $redirects = __DIR__ . "/redirects.php";
  if (file_exists($redirects)) {
    include $redirects;
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
  '^.+lexky-d8\.pantheonsite\.io$',
  '^www\.lexingtonky\.gov$',
);

$config_overrides = __DIR__ . "/config.overrides.php";
if (file_exists($config_overrides)) {
  include $config_overrides;
}

if (! (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live')) {
  $config['system.mail']['interface']['default'] = 'test_mail_collector';
}

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
$settings['install_profile'] = 'standard';

