<?php
namespace Drupal\kong_api_publisher\Utils;

use Drupal\kong_api_publisher\KongAPI\KongHttpRequest;

class Service {
  public static function getServices() {
    $connection = \Drupal::service('database');
    $query = $connection->select('api_spec', 'spec');
    $query->fields('spec', [
      'version',
      'name',
      'service_id',
      'updated_at',
    ]);

    return $query->execute()->fetchAll();
  }

  public static function upsertPolicies($policy) {
    $connection = \Drupal::service('database');
    $config = serialize($policy['config']);
    $query = $connection->merge('policies');
    $query->key([
      'plugin_id' => $policy['id'],
    ])
      ->fields([
        'service_id' => $policy['service']['id'],
        'name' => $policy['name'],
        'enabled' => (int) $policy['enabled'],
        'config' => $config,
      ])
      ->execute();
  }

  public static function getPoliciesByService($service_id) {
    $connection = \Drupal::service('database');
    $query = $connection->select('policies', 'p');

    $query->fields('p', [
      'plugin_id',
      'service_id',
      'name',
      'enabled',
      'config',
    ]);

    $polices = $query->execute()->fetchAll();
    $data = [];
    foreach ($polices as $item) {
      $data[$item->name] = [
        'id' => $item->plugin_id,
        'name' => $item->name,
        'enabled' => $item->enabled,
        'service_id' => $item->service_id,
        'config' => unserialize($item->config),
      ];
    }

    return $data;
  }

  public static function getAllPolicies() {
    $connection = \Drupal::service('database');
    $query = $connection->select('policies', 'p');

    $query->fields('p', [
      'plugin_id',
      'name',
      'service_id',
    ])
      ->fields('s', ['name']);
    $query->join('api_spec', 's', 's.service_id = p.service_id');

    $polices = $query->execute()->fetchAll();

    return $polices;
  }

  public static function deletePolicy($policy_id) {
    $connection = \Drupal::service('database');
    $query = $connection->delete('policies')
      ->condition('plugin_id', $policy_id)
      ->execute();
  }
  public static function deletePolicyByServiceId($service_id) {
    $connection = \Drupal::service('database');
    $query = $connection->delete('policies')
      ->condition('service_id', $service_id)
      ->execute();
  }

  public static function deleteService($service_id) {
    $connection = \Drupal::service('database');
    $query = $connection->delete('api_spec')
      ->condition('service_id', $service_id)
      ->execute();
  }

  public static function deleteKongPolicy($policy_id) {
    $kongHttp = KongHttpRequest::getInstance();
    $kongHttp->deletePlugin($policy_id);
    self::deletePolicy($policy_id);
  }

  public static function deleteKongService($service_id) {
    $kongHttp = KongHttpRequest::getInstance();
    $polices = $kongHttp->getPluginByService($service_id);
    $routes = $kongHttp->getRoutesByService($service_id);
    foreach ($polices->data as $policy) {
      self::deleteKongPolicy($policy->id);
    }

    foreach ($routes->data as $route) {
      $kongHttp->deleteRoute($route->id);
    }

    $kongHttp->deleteService($service_id);
    Self::deleteService($service_id);
  }

}
