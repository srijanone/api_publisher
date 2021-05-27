<?php

namespace Drupal\kong_api_publisher\OAS2Kong\KongEntity;

abstract class OpenAPIKong {
  protected $oas = [];
  protected $declartiveConfig = [
    'services' => [],
    'routes' => [],
  ];

  public function __construct($oas) {
    $this->oas = $oas;
    $this->createDeclarativeConfig();
  }

  public function getServices() {
    return $this->declartiveConfig['services'];
  }

  public function getRoutes() {
    return $this->declartiveConfig['routes'];
  }

  public function getDeclarativeConfig() {
    return $this->declartiveConfig;
  }

  abstract protected function createDeclarativeConfig();
}
