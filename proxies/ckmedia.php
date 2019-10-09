<?php
/**
 * @file
 *
 * Proxy for ckeditor's media extension to call in order to avoid XSS blocks.
 */

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

require_once '../autoload.php';

$request = Request::createFromGlobals();
$client = new Client([
  'headers' => [
    'Referer' => $request->headers->get('referer')
  ]
]);
$response = $client->request('GET', '//ckeditor.iframe.ly/api/oembed', ['query' => [
  'url' => $request->query->get('url'),
  'callback' => $request->query->get('callback')
]]);

echo $response->getBody();
