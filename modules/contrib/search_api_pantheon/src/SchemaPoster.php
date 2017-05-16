<?php

/**
 * @file
 * Post a schema file to to the Pantheon Solr server.
 */

namespace Drupal\search_api_pantheon;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Client;

/**
 * Class SchemaPoster.
 *
 * @package Drupal\search_api_pantheon
 */
class SchemaPoster {

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructor.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, Client $http_client) {
    $this->loggerFactory = $logger_factory;
    $this->httpClient = $http_client;
  }

  /**
   * Post a schema file to to the Pantheon Solr server.
   */
  public function postSchema($schema) {

    // Check for empty schema.
    if (filesize($schema) < 1) {
      $this->loggerFactory->get('search_api_pantheon')->error('Empty schema not posting');
      return NULL;
    }
    // Check for invalid XML.
    $schema_file = file_get_contents($schema);
    if (!@simplexml_load_string($schema_file)) {
      $this->loggerFactory->get('search_api_pantheon')->error('Schema is not XML - not posting');
      return NULL;
    }

    $ch = curl_init();
    $host = getenv('PANTHEON_INDEX_HOST');
    $path = 'sites/self/environments/' . $_ENV['PANTHEON_ENVIRONMENT'] . '/index';

    $client_cert = $_SERVER['HOME'] . '/certs/binding.pem';
    $url = 'https://' . $host . '/' . $path;

    $file = fopen($schema, 'r');
    // Set URL and other appropriate options.
    $opts = array(
      CURLOPT_URL => $url,
      CURLOPT_PORT => getenv('PANTHEON_INDEX_PORT'),
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_SSLCERT => $client_cert,
      CURLOPT_HTTPHEADER => array('Content-type:text/xml; charset=utf-8'),
      CURLOPT_PUT => TRUE,
      CURLOPT_BINARYTRANSFER => 1,
      CURLOPT_INFILE => $file,
      CURLOPT_INFILESIZE => filesize($schema),
    );
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $success_codes = array(
      '200',
      '201',
      '202',
      '204',
    );

    $success = (in_array($info['http_code'], $success_codes));
    fclose($file);
    if (!$success) {
      $this->loggerFactory->get('search_api_pantheon')->error('Schema failed to post');
    }
    else {
      $this->loggerFactory->get('search_api_pantheon')->info('Schema posted');
    }
    return $success;
  }

}
