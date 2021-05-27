<?php

namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\kong_api_publisher\OAS2Kong\KongEntity\OpenAPI2Kong;
use Drupal\kong_api_publisher\OAS2Kong\KongEntity\OpenAPI3Kong;
use Drupal\kong_api_publisher\OAS2Kong\KongHttpRequest;
use Drupal\kong_api_publisher\OAS2Kong\Parser;
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
    $form['oas_version'] = array(
      '#type' => 'select',
      '#title' => $this->t('OpenAPI Spec Version'),
      '#description' => $this->t("Please specify OpenAPI version"),
      '#options' => $this->oas_versions,
      '#default_value' => $this->defaultOasVersion,
      '#required' => true,
    );

    $form['oas_format'] = array(
      '#type' => 'select',
      '#title' => $this->t('OpenAPI format'),
      '#description' => $this->t("Please specify OpenAPI format"),
      '#placeholder' => $this->t('Enter open api spec'),
      '#options' => $this->oas_format,
      '#default_value' => $this->defaultOasFormat,
      '#required' => true,
    );

    $form['oas'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('OpenAPI spec'),
      '#description' => $this->t("Enter OpenAPI spec either JOSN or YML"),
      '#placeholder' => $this->t('Enter open api spec'),
      '#default_value' => '',
      '#rows' => 20,
      '#required' => true,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $spec = $form_state->getValue('oas');
    $version = $form_state->getValue('oas_version');
    $format = $form_state->getValue('oas_format');

    try {
      $parsedSpec = Parser::parse($spec, $format);
      $this->importKongConfig($parsedSpec, $version);
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in importing. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }
  }

  private function importKongConfig($spec, $version) {
    $kongHttp = new KongHttpRequest([
      'base_url' => 'kong:8001',
    ]);
    $kongEntity = null;

    switch ($version) {
      case 2:
        $kongEntity = new OpenAPI2Kong($spec);
        break;
      case 3:
        $kongEntity = new OpenAPI3Kong($spec);
        break;
    }
    dsm($kongEntity->getRoutes());
    $kongHttp->addService($kongEntity->getServices());
    $kongHttp->addRoutes($kongEntity->getRoutes());
    $this->messenger()->addStatus($this->t('Succesfully imported'));
  }
}
