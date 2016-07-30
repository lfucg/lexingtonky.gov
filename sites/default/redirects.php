<?php

/**
 * Get redirects from file.
 */
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
    if ($by_old_path["old_path"]) {
      print("<br>old_path already exists: " . $redirect["old_path"]);
    } else {
      $by_old_path[$redirect["old_path"]] = $redirect["new_path"];
    }
  }
  return $by_old_path;
}

function _lexky_redirect($new_path) {
  $new_url = 'https://www.lexingtonky.gov'.$new_path;
  // print('incoming: '.  $_SERVER['REQUEST_URI']. "<br>");
  // die($new_url);
  header('HTTP/1.0 302 Moved Temporarily');
  header('Location: ' . $new_url);
  exit();
}

function _lexky_get_redirect_from_table($incoming_path) {
  $redirects = _lexky_get_redirects();
  return $redirects[$incoming_path];
}

$incoming_path = $_SERVER['REQUEST_URI'];
$redirect_table_path = _lexky_get_redirect_from_table($incoming_path);

if ($redirect_table_path) {
  _lexky_redirect($redirect_table_path);
} else if (in_array($_SERVER['HTTP_HOST'], ['next.lexingtonky.gov', 'lexingtonky.gov'])) {
  _lexky_redirect($incoming_path);
}
