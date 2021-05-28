<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

class OpenAPI2Kong extends OpenAPIKong {

  use MapServiceAndRoute;

  protected function createDeclarativeConfig() {
    $this->mapServiceAndRoute();
  }
}
