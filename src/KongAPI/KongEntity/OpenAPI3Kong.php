<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

/**
 * Generting kong entity based on
 * OAS v3
 */
class OpenAPI3Kong extends OpenAPIKong {

  use MapServiceAndRoute;

  /**
   * Implement abstract method createDeclarativeConfig
   * as per OAS v2
   */
  protected function createDeclarativeConfig() {
    $this->mapServiceAndRoute();
  }

  /**
   * Mapping service as per OAS v3
   */
  protected function mapService() {
    $this->validateService();

    $url = $this->oas['servers'][0]['url'];
    $parsedUrl = parse_url($url);

    return [
      'name' => $this->sanitizeName($this->oas['info']['title']),
      'host' => $parsedUrl['host'],
      'path' => $parsedUrl['path'],
    ];
  }

  /**
   * Validating the parsed OAS spec before mapping to
   * kong entity
   */
  protected function validateService() {
    if (!isset($this->oas['info']['title'])) {
      throw new \Exception('Please provide title attribute in spec');
    }

    if (!$this->oas['servers'][0]['url']) {
      throw new \Exception('Please provide atleast one servers url');
    }
  }
}
