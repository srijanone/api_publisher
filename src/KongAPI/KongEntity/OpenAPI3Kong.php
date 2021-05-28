<?php

namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

class OpenAPI3Kong extends OpenAPIKong {

  use MapServiceAndRoute;

  protected function createDeclarativeConfig() {
    $this->mapServiceAndRoute();
  }

  protected function mapService() {
    $url = $this->oas['servers'][0]['url'];
    $parsedUrl = parse_url($url);

    return [
      'name' => $this->sanitizeName($this->oas['info']['title']),
      'host' => $parsedUrl['host'],
    ];
  }
}
