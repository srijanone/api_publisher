<?php

namespace Drupal\kong_api_publisher\KongAPI;

/**
 * A Request class to call the Kong Admin API
 */
class KongHttpRequest {
  protected $config;

  /**
   * construct KongHttpRequest object
   *
   * @param $config Array associative array
   */
  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * Creating and Modified kong service
   *
   * @param $service Array as per kong service spec
   */
  public function addService($service) {
    $url = $this->config['base_url'] . '/services/' . $service['name'];
    \Drupal::httpClient()->put($url, [
      'json' => $service,
    ]);
  }

  /**
   * Creating and Modified kong route
   *
   * @param $route Array as per kong route spec
   */
  public function addRoute($route) {
    $url = $this->config['base_url'] . '/routes/' . $route['name'];
    \Drupal::httpClient()->put($url, [
      'json' => $route,
    ]);
  }

  /**
   * Creating and Modified multiple kong route
   *
   * @param $routes Array of route as per kong route spec
   */
  public function addRoutes($routes) {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }
}
