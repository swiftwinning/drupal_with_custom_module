<?php

namespace Drupal\custom_mbta_api_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @package Drupal\custom_mbta_api_module\Controller
 */
class MbtaApiController extends ControllerBase {

  /**
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * @var String
   */
  protected $baseUri = 'https://api-v3.mbta.com/';

  /**
   * MbtaApiController constructor function.
   * @param \Drupal\http_client_manager\HttpClientInterface $http_client
   */
  public function __construct() {
    $this->httpClient = \Drupal::httpClient();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  // This function will become necessary for allowing use by other modules.
  // /**
  //  * @return \Drupal\http_client_manager\HttpClientInterface
  //  */
  // public function getClient() {
  //   return $this->httpClient;
  // }

  /**
   * Returns JSON Http Response to test any basic endpoint of MBTA API
   */
  public function blueBasicInfo() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'routes/Blue'
    );
    return $request;
  }

}
