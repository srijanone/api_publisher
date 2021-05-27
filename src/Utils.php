<?php

namespace Drupal\kong_api_publisher;

class Utils {
  public static function logger() {
    return \Drupal::logger('OAS2Kong');
  }
}
