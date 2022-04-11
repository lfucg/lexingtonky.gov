<?php

namespace Drupal\search_api_pantheon\Solarium;

use Solarium\Core\Client\Adapter\AdapterHelper;
use Solarium\Core\Client\Adapter\Curl;

/**
 * Override Solarium so that more options can be set before executing curl.
 */
class PantheonCurl extends Curl {

  /**
   * {@inheritdoc}
   */
  public function createHandle($request, $endpoint) {
    $handler = parent::createHandle($request, $endpoint);
    if (defined('PANTHEON_ENVIRONMENT')) {
      $uri = AdapterHelper::buildUri($request, $endpoint);

      // Adjust the url from the default calculation. Remove the new url type
      // 'solr/.' and change the ping url.
      $uri = str_replace(['/solr/.', 'admin/ping'], ['', 'admin/system'], $uri);
      curl_setopt($handler, CURLOPT_URL, $uri);

      // Set access options, use SSL Certificate.
      curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, FALSE);
      $client_cert = $_SERVER['HOME'] . '/certs/binding.pem';
      curl_setopt($handler, CURLOPT_SSLCERT, $client_cert);
    }
    return $handler;
  }

}
