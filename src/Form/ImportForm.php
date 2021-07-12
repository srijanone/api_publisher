<?php

namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\kong_api_publisher\KongAPI\KongEntity\OpenAPI2Kong;
use Drupal\kong_api_publisher\KongAPI\KongEntity\OpenAPI3Kong;
use Drupal\kong_api_publisher\KongAPI\KongHttpRequest;
use Drupal\kong_api_publisher\KongAPI\Parser;
use Drupal\kong_api_publisher\Utils;

class ImportForm extends FormBase {

  private $oas_versions = [
    '2' => 'OpeanAPI 2.0/Swagger 2.0',
    '3' => 'OpeanAPI 3.0',
  ];

  private $oas_format = [
    'json' => 'JSON',
    'yml' => 'YML',
  ];

  private $defaultOasVersion = 2;
  private $defaultOasFormat = 'yml';

  public function getFormId() {
    return 'kong_api_publisher_import_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $service_id = \Drupal::routeMatch()->getParameter('service_id');
    $service = $this->getService($service_id);

    $form['oas_format'] = array(
      '#type' => 'select',
      '#title' => $this->t('Open API format '),
      '#description' => $this->t("Please specify OpenAPI format"),
      '#placeholder' => $this->t('Enter open api spec'),
      '#options' => $this->oas_format,
      '#default_value' => $service->format ? $service->format : $this->defaultOasFormat,
      '#required' => true,
    );

    $form['oas_version'] = array(
      '#type' => 'select',
      '#title' => $this->t('Open API Spec Version'),
      '#description' => $this->t("Please specify OpenAPI version"),
      '#options' => $this->oas_versions,
      '#default_value' => $service->oas_version ? $service->oas_version : $this->defaultOasVersion,
      '#required' => true,
    );

    $form['spec_version'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#description' => $this->t("Add a version. Example: 1.0.0"),
      '#default_value' => $service->version,
      '#required' => true,
    );

    $form['oas'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('OpenAPI spec'),
      '#description' => $this->t("Enter OpenAPI spec either JOSN or YML"),
      '#placeholder' => $this->t('Enter open api spec'),
      '#default_value' => $service->spec ? $service->spec : '',
      '#rows' => 20,
      '#required' => true,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $url = Url::fromRoute('kong_api_publisher.services');
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => $url,
      '#title' => $this->t('Cancel'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $spec = $form_state->getValue('oas');
    $oas_version = $form_state->getValue('oas_version');
    $format = $form_state->getValue('oas_format');
    $spec_version = $form_state->getValue('spec_version');

    try {
      // Parse Yml or Json string
      $parsedSpec = Parser::parse($spec, $format);
      // Importing parsed OAS into kong API gateway
      $data = $this->importKongConfig($parsedSpec, $oas_version);
      $service = json_decode($data['service']);

      $connection = \Drupal::service('database');
      $query = $connection->merge('api_spec');
      $query->key([
        'service_id' => $service->id,
      ])
        ->fields([
          'format' => $format,
          'oas_version' => $oas_version,
          'version' => $spec_version,
          'spec' => $spec,
          'name' => $service->name,
          'service_id' => $service->id,
          'updated_at' => time(),
        ])
        ->execute();
      $form_state->setRedirect('kong_api_publisher.services');

    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in importing. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }
  }

  /**
   * Importing OAS spec into kong API gateway
   *
   * @param Array $spec OAS parsed array
   * @param Number $version OAS version i.e. either OAS2 or OAS3
   */
  private function importKongConfig($spec, $version) {
    // $config = \Drupal::config(KongConfigurationForm::CONFIG_ID);

    // // creating kong http request based on configuration
    // $kongHttp = new KongHttpRequest([
    //   'base_url' => $config->get('admin_url'),
    // ]);
    $kongHttp = KongHttpRequest::getInstance();
    $kongEntity = null;

    switch ($version) {
      case 2:
        // creating kong entity for OAS v2
        $kongEntity = new OpenAPI2Kong($spec);
        break;
      case 3:
        // creating kong entity for OAS v3
        $kongEntity = new OpenAPI3Kong($spec);
        break;
    }

    // importing kong services to kong gateway
    $service = $kongHttp->addService($kongEntity->getServices());
    // importing kong routes to kong gateway
    $routes = $kongHttp->addRoutes($kongEntity->getRoutes());
    $this->messenger()->addStatus($this->t('Succesfully imported'));

    return [
      'service' => $service,
      'routes' => $routes,
    ];
  }

  public function getService($service_id) {
    $connection = \Drupal::service('database');
    $query = $connection->select('api_spec', 'spec');
    $query->fields('spec', [
      'format',
      'oas_version',
      'version',
      'spec',
      'name',
      'service_id',
    ])
      ->condition('spec.service_id', $service_id);

    return $query->execute()->fetchObject();
  }
}
