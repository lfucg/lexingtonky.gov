<?php

function _lexky_get_overrides()
{
  $overridesFile = 'sites/default/files/private/config.overrides.json';
  if (!file_exists($overridesFile)) { return; }

  $overridesJson = file_get_contents($overridesFile);
  $overrides = json_decode($overridesJson, 1);
  // if ($overrides == FALSE) { die('Could not parse json in overrides file. Aborting!'); }
  return $overrides;
}

function _lexky_set_overrides(&$config, $to_override) {
  $overrides = _lexky_get_overrides();
  if (! $overrides) { return; }

  foreach ($to_override as $namespace => $keys) {
    foreach ($keys as $key) {
      $config[$namespace][$key] = $overrides[$namespace][$key];
    }
  }
}

_lexky_set_overrides($config, ['smtp.settings' => ['smtp_host', 'smtp_username', 'smtp_password']]);
