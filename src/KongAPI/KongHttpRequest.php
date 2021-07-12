<?php

namespace Drupal\kong_api_publisher\KongAPI;

use Drupal\kong_api_publisher\Form\KongConfigurationForm;

/**
 * A Request class to call the Kong Admin API
 */
class KongHttpRequest {
  protected $config;
  protected $http;

  private static $instance;

  /**
   * construct KongHttpRequest object
   *
   * @param $config Array associative array
   */
  protected function __construct($config) {
    $this->config = $config;
    $auth = 'Basic ' . base64_encode($config['username'] . ':' . $config['password']);
    $this->config['options'] = [
      'headers' => [
        'Authorization' => $auth,
      ],
    ];
  }

  public static function getInstance() {
    if (!isset(self::$instance)) {
      $config = \Drupal::config(KongConfigurationForm::CONFIG_ID);
      self::$instance = new static([
        'base_url' => $config->get('admin_url'),
        'username' => $config->get('username'),
        'password' => $config->get('password'),
      ]);
    }

    return self::$instance;
  }

  /**
   * Creating and Modified kong service
   *
   * @param $service Array as per kong service spec
   */
  public function addService($service) {
    $url = $this->config['base_url'] . '/services/' . $service['name'];

    $options = $this->config['options'];
    $options['json'] = $service;

    return \Drupal::httpClient()->put($url, $options)->getBody()->getContents();
  }

  /**
   * Creating and Modified kong route
   *
   * @param $route Array as per kong route spec
   */
  public function addRoute($route) {
    $url = $this->config['base_url'] . '/routes/' . $route['name'];

    $options = $this->config['options'];
    $options['json'] = $route;

    return \Drupal::httpClient()->put($url, $options)->getBody()->getContents();
  }

  /**
   * Creating and Modified multiple kong route
   *
   * @param $routes Array of route as per kong route spec
   */
  public function addRoutes($routes) {
    $res = [];
    foreach ($routes as $route) {
      $res[] = $this->addRoute($route);
    }

    return $res;
  }

  /**
   * Creating and Modified kong plugin
   *
   * @param $plugin Array as per kong plugin spec
   */
  public function addPlugin($plugin) {
    $uuid = $plugin['id'];
    $url = $this->config['base_url'] . '/plugins/' . $uuid;

    $options = $this->config['options'];
    $options['json'] = $plugin;

    return \Drupal::httpClient()->put($url, $options)->getBody()->getContents();
  }

  /**
   * Delete Plugin
   *
   * @param $plugin_id
   */
  public function deletePlugin($plugin_id) {
    $url = $this->config['base_url'] . '/plugins/' . $plugin_id;

    return \Drupal::httpClient()->delete($url, $this->config['options'])->getBody()->getContents();
  }

  /**
   * Delete service
   *
   * @param $service_id
   */
  public function deleteService($service_id) {
    $url = $this->config['base_url'] . '/services/' . $service_id;

    return \Drupal::httpClient()->delete($url, $this->config['options'])->getBody()->getContents();
  }

  /**
   * Delete route
   *
   * @param $route_id
   */
  public function deleteRoute($route_id) {
    $url = $this->config['base_url'] . '/routes/' . $route_id;

    return \Drupal::httpClient()->delete($url, $this->config['options'])->getBody()->getContents();
  }

  /**
   * Get Routes assoociaated to a Specific Service
   *
   * @param $service_id
   */
  public function getRoutesByService($service_id) {
    $url = $this->config['base_url'] . '/services/' . $service_id . '/routes';

    $res = \Drupal::httpClient()->get($url, $this->config['options'])->getBody()->getContents();

    return json_decode($res);
  }
  /**
   * Get Plugin/policy assoociaated to a Specific Service
   *
   * @param $service_id
   */
  public function getPluginByService($service_id) {
    $url = $this->config['base_url'] . '/services/' . $service_id . '/plugins';
    $res = \Drupal::httpClient()->get($url, $this->config['options'])->getBody()->getContents();

    return json_decode($res);
  }

}
