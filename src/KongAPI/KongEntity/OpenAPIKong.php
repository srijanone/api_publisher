<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

/**
 * Abstract class for open API kong mapping
 */
abstract class OpenAPIKong {
  protected $oas = [];
  protected $kongEntities = [
    'services' => [],
    'routes' => [],
  ];

  /**
   * construct accept parsed Open API spec
   *
   * @param Array $oas parsed Open API spec
   */
  public function __construct($oas) {
    $this->oas = $oas;
    $this->createDeclarativeConfig();
  }

  /**
   * Get services
   *
   * @return Array services
   */
  public function getServices() {
    return $this->kongEntities['services'];
  }

  /**
   * Set services
   *
   * @return Array $services
   */
  public function setServices($services) {
    $this->kongEntities['services'] = $services;
  }

  /**
   * Get routes
   *
   * @return Array routes
   */
  public function getRoutes() {
    return $this->kongEntities['routes'];
  }

  /**
   * Set routes
   *
   * @return Array $routes
   */
  public function setRoutes($routes) {
    $this->kongEntities['routes'] = $routes;
  }

  /**
   * Get kong entities
   *
   * @return Array kong entities
   */
  public function getKongEntities() {
    return $this->kongEntities;
  }

  /**
   * Abstract method should be implemented as per
   * Open API spec version
   *
   * @return Array kong entities
   */
  abstract protected function createDeclarativeConfig();
}
