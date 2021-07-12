<?php
namespace Drupal\kong_api_publisher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\kong_api_publisher\Utils;
use Drupal\kong_api_publisher\Utils\Service;

/**
 * An example controller.
 */
class ServiceListForm extends FormBase {
  /**
   * Returns a render-able array for a test page.
   */

  public function getFormId() {
    return 'kong_api_publisher_service_listing';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $services = Service::getServices();
    // $kongHttp = KongHttpRequest::getInstance();
    // $p = $kongHttp->getPluginByService('1f7ec11f-2d5f-4edb-81da-2d927ef764fc');
    // dsm($p);
    $header = [
      'service_name' => $this->t('Service Name'),
      'version' => $this->t('Version'),
      'last_updated' => $this->t('Last Updated'),
      'operations' => $this->t('Operations'),
    ];

    $options = [];
    $date_formatter = \Drupal::service('date.formatter');

    foreach ($services as $row) {
      $options[$row->service_id] = [
        'service_name' => $row->name,
        'version' => $row->version,
        'last_updated' => $date_formatter->format($row->updated_at, 'date_with_full_month_and_day'),
        'operations' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('kong_api_publisher.edit_publish_api', ['service_id' => $row->service_id]),
              ],
              'polices' => [
                'title' => $this->t('Attach Policy'),
                'url' => Url::fromRoute('kong_api_publisher.attach_map_policy', ['service_id' => $row->service_id]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('kong_api_publisher.delete_service', ['service_id' => $row->service_id]),
              ],
            ],
          ],
        ],
      ];
    }

    $form['#prefix'] = '<div id="service-form-warpper">';
    $form['#suffix'] = '</div>';
    $form['add_text'] = [
      '#markup' => 'Publish Drupal APIs using Kong',
      '#prefix' => '<div class="add-action">',
    ];

    $link = Link::createFromRoute($this->t('Add'), 'kong_api_publisher.publish_api');
    $form['add_markup'] = [
      '#markup' => $link->toString(),
      '#suffix' => '</div> <hr>',
    ];

    $form['add_form'] = [
      '#markup' => '<div id="add_form"></div>',
    ];

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

    // $form['#attached']['library'][] = 'kong_api_publisher/kong_api_publisher';

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $service_ids = [];
    foreach ($form_state->getValue('table') as $key => $value) {
      if ($key === $value) {
        $service_ids[] = $key;
      }
    }
    if (count($service_ids) <= 0) {
      $this->messenger()->addError($this->t('Please select at least one service'));
      $form_state->setRebuild();

      return;
    }

    try {
      foreach ($service_ids as $service_id) {
        Service::deleteKongService($service_id);
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in deleting service. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }

  }

}
