<?php

namespace Drupal\kong_api_publisher\OAS2Kong\KongEntity;

trait MapServiceAndRoute {

  protected function mapServiceAndRoute() {
    $services = $this->mapService();
    $routes = $this->mapRoute($services['name']);
    $this->declartiveConfig['services'] = $services;
    $this->declartiveConfig['routes'] = $routes;
  }

  protected function mapRoute($serviceName) {
    $routes = [];
    foreach ($this->oas['paths'] as $path => $items) {
      foreach ($items as $method => $route) {
        $routes[] = [
          'service' => ['name' => $serviceName],
          'name' => $this->sanitizeName($route['operationId']),
          'methods' => [strtoupper($method)],
          'paths' => [$path],
        ];
      }
    }

    return $routes;
  }

  protected function mapService() {
    return [
      'name' => $this->sanitizeName($this->oas['info']['title']),
      'host' => $this->oas['host'],
      'path' => $this->oas['basePath'],
    ];
  }

  protected function sanitizeName($name) {
    $name = explode(' ', $name);

    return implode('-', $name);
  }
}
