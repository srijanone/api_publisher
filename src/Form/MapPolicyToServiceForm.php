<?php

namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\kong_api_publisher\Utils;
use Drupal\kong_api_publisher\Utils\PolicyConfig;
use Drupal\kong_api_publisher\Utils\Service;

/**
 * An example controller.
 */
class MapPolicyToServiceForm extends FormBase {
  use PolicyConfig;
  /**
   * Returns a render-able array for a test page.
   */

  public function getFormId() {
    return 'kong_api_publisher_map_policy';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $service_id = \Drupal::routeMatch()->getParameter('service_id');
    $selected_policy = \Drupal::request()->query->get('policy_name');
    $selected_policy = $selected_policy ? 'edit-' . $selected_policy : 'edit-cors';
    $services = Service::getServices();
    $polices = Service::getPoliciesByService($service_id);

    $options = ['My Service'];

    foreach ($services as $service) {
      $options[$service->service_id] = $service->name;
    }

    $form['services'] = [
      '#type' => 'select',
      '#title' => $this->t('Service'),
      '#options' => $options,
      '#default_value' => $service_id ? $service_id : '',
      '#required' => true,
    ];

    $form['policies_tabs'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => $selected_policy,
    );

    $form['cors'] = $this->corsConfigs($polices['cors']);
    $form['rate_limiting'] = $this->rateLimitConfigs($polices['rate-limiting']);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $url = Url::fromRoute('kong_api_publisher.policy_management');
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => $url,
      '#title' => $this->t('Cancel'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {

      $corsRes = $this->corsSubmit($form, $form_state);
      $this->rateLimitingSubmit($form, $form_state);
      $form_state->setRedirect('kong_api_publisher.policy_management');

    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in apply policy. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
      $form_state->setRebuild();
    }
  }

}
