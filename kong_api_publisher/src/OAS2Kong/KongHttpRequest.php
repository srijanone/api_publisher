<?php

namespace Drupal\kong_api_publisher\OAS2Kong;

class KongHttpRequest {
  protected $config;

  public function __construct($config) {
    $this->config = $config;
  }

  public function addService($service) {
    $url = $this->config['base_url'] . '/services/' . $service['name'];
    \Drupal::httpClient()->put($url, [
      'json' => $service,
    ]);
  }

  public function addRoute($route) {
    $url = $this->config['base_url'] . '/routes/' . $route['name'];
    \Drupal::httpClient()->put($url, [
      'json' => $route,
    ]);
  }

  public function addRoutes($routes) {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }
}
