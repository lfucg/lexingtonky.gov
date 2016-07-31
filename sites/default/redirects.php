<?php

function _lexky_get_redirects()
{
  $redirectsFile = 'sites/default/redirects.json';
  if (!file_exists($redirectsFile)) {
    die('No redirects file found. Aborting!');
  }
  $redirectsJson = file_get_contents($redirectsFile);
  $redirects = json_decode($redirectsJson, 1);
  if ($redirects == FALSE) {
    die('Could not parse json in redirects file. Aborting!');
  }
  $by_old_path = [];
  foreach($redirects as $redirect) {
    if ($by_old_path[$redirect["old_path"]]) {
      die("<br>old_path already exists: " . $redirect["old_path"]);
    } else {
      $by_old_path[$redirect["old_path"]] = $redirect["new_path"];
    }
  }
  return $by_old_path;
}

function _lexky_is_sane_redirect($new_url) {
  $current_http = _lexky_pantheon_https_needed() ? 'http' : 'https';
  $current_url =  $current_http . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  return (strtolower($new_url) != strtolower($current_url));
}

function _lexky_desired_http() {
  return (isset($_ENV['PANTHEON_ENVIRONMENT']) ? 'https' : 'http');
}

function _lexky_desired_host() {
  if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
    // enable for cutover: www.lexingtonky.gov
    return 'next.lexingtonky.gov';
  } else {
    return $_SERVER['HTTP_HOST'];
  }
}

function _lexky_redirect($new_path) {
  $new_url = _lexky_desired_http() . '://' . _lexky_desired_host() . $new_path;

  if (_lexky_is_sane_redirect($new_url)) {
    header('HTTP/1.0 302 Moved Temporarily');
    header('Location: ' . $new_url);
    exit();
  }
}

function _lexky_get_redirect_from_table($incoming_path) {
  $redirects = _lexky_get_redirects();
  return $redirects[$incoming_path];
}

function _lexky_pantheon_https_needed() {
  if (isset($_SERVER['PANTHEON_ENVIRONMENT']) &&
    $_SERVER['HTTPS'] === 'OFF') {
    if (!isset($_SERVER['HTTP_X_SSL']) ||
      (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] != 'ON')) {
      return true;
    }
  }
}

$incoming_path = $_SERVER['REQUEST_URI'];
$redirect_table_path = _lexky_get_redirect_from_table($incoming_path);

if ($redirect_table_path) {
  _lexky_redirect($redirect_table_path);

// enable for cutover
// } else if (in_array($_SERVER['HTTP_HOST'], ['next.lexingtonky.gov', 'lexingtonky.gov'])) {
  // redirect to www.lexingtonky.gov
  // _lexky_redirect($incoming_path);

} else if (_lexky_pantheon_https_needed()) {
  _lexky_redirect($_SERVER['REQUEST_URI']);
}
