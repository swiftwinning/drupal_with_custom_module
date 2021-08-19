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
   * Returns JSON Http Response to test a basic endpoint of MBTA API
   */
  public function blueBasicInfo() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'routes/Blue'
    );
    return $request;
  }

  /**
   * Returns Table of all MBTA routes with official colors.
   *   Note: does not use 'sort=description'/'sort=-description' option
   *   Default sort order may be preferable. Discuss options with stakeholders.
   */
  public function routesWithCss() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'routes?fields[route]=color,text_color,long_name',
    );
    $routeInfo = $request->getBody()->getContents();
    $routeArray = json_decode($routeInfo, true)['data'];

    $stringOfRowElements = '';

    foreach ($routeArray as $route) {
      $color = $route['attributes']['color'];
      $text_color = $route['attributes']['text_color'];
      $long_name = $route['attributes']['long_name'];

      $newFormattedRow = '<tr class="color-'.$color.' text-color-'.$text_color.'">
        <td>'.$long_name.'</td>
      </tr>';
      $stringOfRowElements = $stringOfRowElements.$newFormattedRow;
    }

    $build[] = [
      '#markup' => '<table>'.$stringOfRowElements.'</table>',
      '#attached' => [
        'library' => [
          'custom_mbta_api_module/custom_mbta_api_module.mbta-route-colors',
        ],
      ],
    ];
    return ($build);
  }

  /**
   * Returns JSON Http Response, full schedule of the route with specified ID
   *   Stub to test route links, until functionality to display formatted schedules
   */
  public function routeScheduleJson($id) {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'schedules?filter[route]='.$id
    );
    return $request;
  }



}
