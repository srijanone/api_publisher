<?php
namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\kong_api_publisher\Utils\Service;

/**
 * An example controller.
 */
class PolicyManagementForm extends FormBase {
  /**
   * Returns a render-able array for a test page.
   */

  public function getFormId() {
    return 'kong_api_publisher_policy_management';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $polices = Service::getAllPolicies();

    $header = [
      'policy' => $this->t('Policy'),
      'service' => $this->t('Service Name'),
      'operations' => $this->t('Operations'),
    ];

    $options = [];

    foreach ($polices as $row) {
      $options[$row->plugin_id] = [
        'policy' => $row->name,
        'service' => $row->s_name,
        'operations' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('kong_api_publisher.attach_map_policy', ['service_id' => $row->service_id], ['query' => ['policy_name' => $row->name]]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('kong_api_publisher.delete_policy', ['policy_id' => $row->plugin_id]),
              ],
            ],
          ],
        ],
      ];
    }

    $form['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#prefix' => '<div class="service-list">',
    ];

    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this
        ->t('No services found'),
      '#suffix' => '</div>',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $policy_ids = [];
    foreach ($form_state->getValue('table') as $key => $value) {
      if ($key === $value) {
        $policy_ids[] = $key;
      }
    }
    if (count($policy_ids) <= 0) {
      $this->messenger()->addError($this->t('Please select at least one policy'));
      // $form_state->setRebuild();

      return;
    }
    try {
      foreach ($policy_ids as $policy_id) {
        Service::deleteKongPolicy($policy_id);
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in deleting polices. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }

  }

}
