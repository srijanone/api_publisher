<?php
namespace Drupal\kong_api_publisher\KongAPI\KongEntity;

class KongPlugin {
  protected $plugin = [
    'name' => null,
    'route' => null,
    'service' => null,
    'enable' => true,
    'tags' => [],
    'protocols' => ["http", "https"],
    'config' => [],
  ];

  public function setEnable($enable) {
    $this->plugin['enable'] = $enable;
  }

  public function setProtocols($protocols) {
    $this->plugin['protocols'] = $protocols;
  }

  public function setTags($tags) {
    $this->plugin['tags'] = $tags;
  }
}
