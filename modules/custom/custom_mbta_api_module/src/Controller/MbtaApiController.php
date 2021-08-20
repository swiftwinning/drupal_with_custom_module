<?php

namespace Drupal\custom_mbta_api_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

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
  protected $baseUri = 'https://api-v3.mbta.com';

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
   * Returns Table of all MBTA routes with official colors.
   *   Note: 'routesWithCss()' & 'routesWithLinks()' do not use
   *   'sort=description'/'sort=-description' options.
   *   Default sort order may be preferable. Discuss sorting options with stakeholders.
   */
  public function routesWithCss() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'/routes?fields[route]=color,text_color,long_name',
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
   * Returns Table of all MBTA routes, with links from each route to its schedule.
   *   Note: 'routesWithCss()' & 'routesWithLinks()' do not use
   *   'sort=description'/'sort=-description' options.
   *   Default sort order may be preferable. Discuss sorting options with stakeholders.
   */
  public function routesWithLinks() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'/routes?fields[route]=color,text_color,long_name',
    );
    $response = $request->getBody()->getContents();
    $routeArray = json_decode($response, true)['data'];

    $arrayOfRowElements = [];

    foreach ($routeArray as $route) {
      $long_name = $route['attributes']['long_name'];
      $idString = $route['id'];
      $url = Url::fromRoute(
        'custom_mbta_api_module.basic_schedule_table', ['id' => $idString]
      );
      $link_to_route = [
        \Drupal::l(t($long_name), $url),
      ];
      $arrayOfRowElements[] = $link_to_route;
    }

    $build[] = [
      '#type' => 'table',
      '#header' => [$this->t('Select a route')],
      '#rows' => $arrayOfRowElements,
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
      $this->baseUri.'/schedules?filter[route]='.$id
    );
    return $request;
  }

  public function basicScheduleTable($id) {
    try {
      $request = $this->httpClient->request(
        'GET',
        $this->baseUri.'/schedules?filter[route]='.$id.'&page[limit]=100',
      );
    } catch (RequestException $e) {
      //  Do proper error handling.
    }
    $response = $request->getBody()->getContents();
    $scheduleArray = json_decode($response, true)['data'];

    $rows = [];

    foreach ($scheduleArray as $stop) {
      $arrival_time = $stop['attributes']['arrival_time'];
      $departure_time = $stop['attributes']['departure_time'];
      $stopId = $stop['relationships']['stop']['data']['id'];
      if (!is_null($departure_time)) {
        $timestamp = $departure_time;
      } else {
        $timestamp = $arrival_time;
      }
      $rows[] = [$stopId, $timestamp];
    }

    $build[] = [
      '#type' => 'page',
      'content' => [
        '#type' => 'table',
        '#caption' => $this->t('Test API response: '.$id.' route basic schedule'),
        '#header' => [$this->t('Stop Id'), $this->t('Time')],
        '#rows' => $rows,
      ],
    ];
    return $build;
  }

  /**
   * Returns JSON Http Response to test a basic endpoint of MBTA API
   */
  public function blueBasicInfo() {
    $request = $this->httpClient->request(
      'GET',
      $this->baseUri.'/routes/Blue'
    );
    return $request;
  }

}
