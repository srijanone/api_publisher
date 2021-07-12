<?php
namespace Drupal\kong_api_publisher\Utils;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\kong_api_publisher\KongAPI\KongHttpRequest;

trait PolicyConfig {
  use StringTranslationTrait;

  public function rateLimitConfigs($config = []) {

    return [
      '#type' => 'details',
      '#title' => t('Rate Limit configuration'),
      '#collapsible' => true,
      '#group' => 'policies_tabs',

      'ratelimiting_name' => [
        '#type' => 'hidden',
        '#value' => 'rate-limiting',
      ],

      'ratelimiting_id' => [
        '#type' => 'hidden',
        '#value' => isset($config['id']) ? $config['id'] : \Drupal::service('uuid')->generate(),
      ],

      'ratelimiting_second' => [
        '#type' => 'textfield',
        '#title' => $this->t('Seconds'),
        '#default_value' => isset($config['config']['second']) ? $config['config']['second'] : '',
        '#description' => $this->t("The number of HTTP requests that can be made per second."),
      ],

      'ratelimiting_enable' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Enable Rate Limit policy."),
        '#default_value' => isset($config['enabled']) ? $config['enabled'] : false,
      ],
    ];
  }

  public function rateLimitingSubmit(array $form, FormStateInterface $form_state) {
    $second = (int) $form_state->getValue('ratelimiting_second');

    $data = [
      'id' => $form_state->getValue('ratelimiting_id'),
      'name' => $form_state->getValue('ratelimiting_name'),
      'service' => ['id' => $form_state->getValue('services')],
      'enabled' => $form_state->getValue('ratelimiting_enable') ? true : false,
      'config' => [
        'second' => $second,
      ],

    ];

    if ($second > 0) {
      $kongHttp = KongHttpRequest::getInstance();

      $res = $kongHttp->addPlugin($data);
      Service::upsertPolicies($data);

      return $res;
    }
  }

  public function corsConfigs($config = []) {
    return [
      '#type' => 'details',
      '#title' => t('CORS configuration'),
      '#group' => 'policies_tabs',
      '#collapsible' => true,

      'cors_name' => [
        '#type' => 'hidden',
        '#value' => 'cors',
      ],
      'cors_id' => [
        '#type' => 'hidden',
        '#value' => isset($config['id']) ? $config['id'] : \Drupal::service('uuid')->generate(),
      ],
      'cors_origins' => [
        '#type' => 'textarea',
        '#title' => $this->t('Origins'),
        '#default_value' => isset($config['config']['origins']) ? implode(PHP_EOL, $config['config']['origins']) : '',
        '#description' => $this->t("List of allowed domains for the Access-Control-Allow-Origin header. If you want to allow all origins, add * as a single value to this configuration field"),
      ],
      'cors_methods' => [
        '#type' => 'textarea',
        '#title' => $this->t('Methods'),
        '#default_value' => isset($config['config']['methods']) ? implode(PHP_EOL, $config['config']['methods']) : '',
        '#description' => $this->t("Value for the Access-Control-Allow-Methods header. Available options include GET, HEAD, PUT, PATCH, POST, DELETE, OPTIONS, TRACE, CONNECT. By default, all options are allowed."),
      ],
      'cors_headers' => [
        '#type' => 'textarea',
        '#title' => $this->t('Headers'),
        '#default_value' => isset($config['config']['headers']) ? implode(PHP_EOL, $config['config']['headers']) : '',
        '#description' => $this->t("Value for the Access-Control-Allow-Headers header."),
      ],

      'cors_enable' => [
        '#type' => 'checkbox',
        '#title' => $this->t("Enable CORS policy."),
        '#default_value' => isset($config['enabled']) ? $config['enabled'] : false,
      ],
    ];
  }

  public function corsSubmit(array $form, FormStateInterface $form_state) {
    $origins = $form_state->getValue('cors_origins');
    $methods = $form_state->getValue('cors_methods');
    $headers = $form_state->getValue('cors_headers');
    $origins = $origins ? explode(PHP_EOL, $origins) : [];
    $methods = $methods ? explode(PHP_EOL, $methods) : [];
    $headers = $headers ? explode(PHP_EOL, $headers) : [];

    $data = [
      'id' => $form_state->getValue('cors_id'),
      'name' => $form_state->getValue('cors_name'),
      'service' => ['id' => $form_state->getValue('services')],
      'enabled' => (bool) $form_state->getValue('cors_enable'),
      'config' => [
        'origins' => $origins,
        'methods' => $methods,
        'headers' => $headers,
      ],

    ];
    $kongHttp = KongHttpRequest::getInstance();

    $res = $kongHttp->addPlugin($data);
    Service::upsertPolicies($data);

    return $res;
  }
}
