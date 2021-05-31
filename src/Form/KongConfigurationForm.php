<?php

namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class KongConfigurationForm extends ConfigFormBase {

  public const CONFIG_ID = 'kong_api_publisher.settings';

  public function getFormId() {
    return 'kong_api_publisher_config_form';
  }

  public function getEditableConfigNames() {
    return [
      KongConfigurationForm::CONFIG_ID,
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(KongConfigurationForm::CONFIG_ID);

    $form['kong'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Configure kong admin API'),
      '#collapsible' => false,
      '#collapsed' => false,
    );

    $form['kong']['admin_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Kong admin API'),
      '#description' => $this->t('Enter kong admin URL i.e. localhost:8001'),
      '#default_value' => $config->get('admin_url'),
      '#required' => true,
    );

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(KongConfigurationForm::CONFIG_ID)
      ->set('admin_url', $form_state->getValue('admin_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
