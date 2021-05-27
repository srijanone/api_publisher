<?php

namespace Drupal\kong_api_publisher\OAS2Kong;

use Symfony\Component\Yaml\Yaml;

/**
 * Parser of JSON and YML
 */
class Parser {

  public static function parse($data, $format) {
    switch ($format) {
      case 'json':
        return Parser::parseJson($data);
      case 'yml':
        return Parser::parseYml($data);
      default:
        return [];
    }
  }

  public static function parseJson($data) {
    return json_decode($data, true);
  }

  public static function parseYml($data) {
    return Yaml::parse($data);
  }

  public static function dump($data) {
    return Yaml::dump($data);
  }
}
