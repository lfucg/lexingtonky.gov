<?php

$custom_domain = $_SERVER['HTTP_HOST'];

if (strpos($custom_domain, 'covid19renterhelp') !== false) {
  header('HTTP/1.0 301 Moved Permanently');
  header('Location: https://www.lexingtonky.gov/renthelp');
  exit();
}


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
    if (strtolower($by_old_path[$redirect["old_path"]])) {
      die("<br>old_path already exists: " . $redirect["old_path"]);
    } else {
      $by_old_path[strtolower($redirect["old_path"])] = $redirect["new_path"];
    }
  }
  return $by_old_path;
}

function _lexky_current_http() {
  if (_lexky_pantheon()) {
    return _lexky_pantheon_http() ? 'http' : 'https';
  } else {
    return 'http';
  }
}

function _lexky_is_sane_redirect($new_url) {
  $current_url =  _lexky_current_http() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  return (strtolower($new_url) != strtolower($current_url));
}

function _lexky_pantheon() {
  return isset($_ENV['PANTHEON_ENVIRONMENT']);
}

function _lexky_desired_http() {
  return (isset($_ENV['PANTHEON_ENVIRONMENT']) ? 'https' : 'http');
}

function _lexky_desired_host() {
  if (isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
    return 'www.lexingtonky.gov';
  } else {
    return $_SERVER['HTTP_HOST'];
  }
}

function _lexky_status_permanent() {
  return '301 Moved Permanently';
}

function _lexky_status_temporary() {
  return '302 Moved Temporarily';
}

function _lexky_internal_redirect_status($current_path) {
  if (strpos($current_path, '/index.aspx') === 0) {
    return _lexky_status_permanent();
  }
  return _lexky_status_temporary();
}

function _lexky_redirect($new_path, $http_status) {
  if (strpos($new_path, 'http') === 0) {
    $new_url = $new_path;
  } else {
    $new_url = _lexky_desired_http() . '://' . _lexky_desired_host() . $new_path;
  }

  if (_lexky_is_sane_redirect($new_url)) {
    header('HTTP/1.0 ' . $http_status);
    header('Location: ' . $new_url);
    exit();
  }
}

function _lexky_get_redirect_from_table($incoming_path) {
  $redirects = _lexky_get_redirects();
  return $redirects[strtolower($incoming_path)];
}

function _lexky_pantheon_http() {
  if (isset($_SERVER['PANTHEON_ENVIRONMENT']) &&
    $_SERVER['HTTPS'] === 'OFF') {
    if (!isset($_SERVER['HTTP_X_SSL']) ||
      (isset($_SERVER['HTTP_X_SSL']) && $_SERVER['HTTP_X_SSL'] != 'ON')) {
      return true;
    }
  }
}

function _lexky_get_legacy_document_redirect($incoming_path) {
  if (strpos($incoming_path, "/Modules/") === 0) {
    return 'http://previous.lexingtonky.gov' . str_replace('?', '@', $incoming_path);
  }
}
// $incoming_path = $_SERVER['REQUEST_URI'];

// $legacy_document_redirect = _lexky_get_legacy_document_redirect($incoming_path);
// $redirect_table_path = _lexky_get_redirect_from_table($incoming_path);

if ($legacy_document_redirect) {
  _lexky_redirect($legacy_document_redirect, _lexky_status_temporary());
} else if ($redirect_table_path) {
  _lexky_redirect($redirect_table_path, _lexky_internal_redirect_status($incoming_path));
} else if (in_array($_SERVER['HTTP_HOST'], ['next.lexingtonky.gov', 'lexingtonky.gov'])) {
  // redirect to www.lexingtonky.gov
  _lexky_redirect($incoming_path, _lexky_status_permanent());
} else if (_lexky_pantheon_http()) {
  _lexky_redirect($_SERVER['REQUEST_URI'], _lexky_status_permanent());
}
