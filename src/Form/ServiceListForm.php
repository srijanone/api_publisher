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

    $doc_link = Link::fromTextAndUrl($this->t('Click'), Url::fromUri('https://docs.konghq.com/gateway-oss/2.4.x/proxy/#terminology'));
    $form['#prefix'] = '<div id="service-form-warpper"><h2>Services</h2> Service as the name implies, are abstractions of each of your own upstream services. Examples of Services would be a data transformation microservice, a billing API, etc. for reference ' . $doc_link->toString();
    $form['#suffix'] = '</div>';
    $form['add_text'] = [
      // '#markup' => '<h4>Publish Drupal APIs using Kong</h4>',
      '#prefix' => '<div class="add-action">',
    ];
    $form['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#submit' => ['::add_service_submit_handler'],
      '#suffix' => '</div> <hr>',
    ];

    $form['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#attributes' => ['class' => ['delete_btn']],
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

    $form['#attached']['library'][] = 'kong_api_publisher/kong_api_publisher';

    return $form;
  }

  public function add_service_submit_handler(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('kong_api_publisher.publish_api');
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
