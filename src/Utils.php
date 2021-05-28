<?php

namespace Drupal\kong_api_publisher;

class Utils {
  public static function logger() {
    return \Drupal::logger('kong_api_publisher');
  }
}
