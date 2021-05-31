<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

/**
 * Generting kong entity based on
 * OAS v2 aka Swagger 2.0
 */
class OpenAPI2Kong extends OpenAPIKong {

  use MapServiceAndRoute;

  /**
   * Implement abstract method createDeclarativeConfig
   * as per OAS v2
   */
  protected function createDeclarativeConfig() {
    $this->mapServiceAndRoute();
  }
}
