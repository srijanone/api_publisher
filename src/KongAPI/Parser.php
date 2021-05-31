<?php

namespace Drupal\kong_api_publisher\KongAPI;

use Symfony\Component\Yaml\Yaml;

/**
 * Parser of JSON and YML
 */
class Parser {

  /**
   * Parse Json Or Yml string
   *
   * @param String $data Yml or Json format
   * @param String $format either json or yml
   *
   * @return Array Parsed data
   */
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

  /**
   * Parse Json
   *
   * @param String $data Json format
   *
   * @return Array Parsed data
   */
  public static function parseJson($data) {
    return json_decode($data, true);
  }

  /**
   * Parse Yml
   *
   * @param String $data yml format
   *
   * @return Array Parsed data
   */
  public static function parseYml($data) {
    return Yaml::parse($data);
  }

  /**
   * Generated Yml from Associative array
   *
   * @param Array $data
   *
   * @return String yaml format
   */
  public static function dump($data) {
    return Yaml::dump($data);
  }
}
