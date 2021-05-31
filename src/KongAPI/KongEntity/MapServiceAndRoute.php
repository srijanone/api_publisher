<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

/**
 * Default implementaion of mapping OAS v2 to kong entity
 * It's a default implementaion based on OAS v2 aka Swagger 2
 */
trait MapServiceAndRoute {

  /**
   * Mapping OAS2 spec to kong service and routes
   */
  protected function mapServiceAndRoute() {
    $services = $this->mapService();
    $routes = $this->mapRoutes($services['name']);
    $this->setServices($services);
    $this->setRoutes($routes);
  }

  /**
   * Mapping OAS2 spec paths to kong routes
   *
   * @param String $serviceName service name to which routes belong
   */
  protected function mapRoutes($serviceName) {
    $routes = [];
    foreach ($this->oas['paths'] as $path => $items) {
      foreach ($items as $method => $route) {
        $routes[] = $this->mapRoute([
          'service' => $serviceName,
          'operationId' => $route['operationId'],
          'path' => $path,
          'method' => $method,
        ]);
      }
    }

    return $routes;
  }

  /**
   * Mapping single paths to kong route
   *
   * @param Array $route associate route info
   */
  protected function mapRoute($route) {
    $this->validateRoute($route);

    return [
      'service' => ['name' => $route['service']],
      'name' => $this->sanitizeName($route['operationId']),
      'methods' => [strtoupper($route['method'])],
      'paths' => [$route['path']],
    ];
  }

  /**
   * Mapping OAS2 to kong service
   *
   */
  protected function mapService() {
    $this->validateService();

    return [
      'name' => $this->sanitizeName($this->oas['info']['title']),
      'host' => $this->oas['host'],
      'path' => $this->oas['basePath'],
    ];
  }

  /**
   * Utils function to sanitize text as per kong entity name attribute
   *
   * @param String $name
   *
   * @return String sanitized string
   */
  protected function sanitizeName($name) {
    $name = explode(' ', $name);

    return implode('-', $name);
  }

  /**
   * Validation check for kong route
   *
   * @param Array $route associative array
   */
  protected function validateRoute($route) {
    $keysToCheck = ['service', 'operationId', 'method', 'path'];
    foreach ($keysToCheck as $key) {
      if (!isset($route[$key])) {
        throw new \Exception('Please provide ' . $key . ' attributes in paths');
      }
    }
  }

  /**
   * Validation check for kong service
   *
   */
  protected function validateService() {
    $keysToCheck = ['host', 'basePath'];

    foreach ($keysToCheck as $key) {
      if (!isset($this->oas[$key])) {
        throw new \Exception('Please provide ' . $key . ' attributes in spec');
      }
    }

    if (!isset($this->oas['info']['title'])) {
      throw new \Exception('Please provide title attribute in spec');
    }
  }
}
