<?php
namespace Drupal\kong_api_publisher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\kong_api_publisher\Utils;
use Drupal\kong_api_publisher\Utils\Service;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * An example controller.
 */
class ServiceController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function deletePolicy() {
    $policy_id = \Drupal::routeMatch()->getParameter('policy_id');
    try {
      Service::deleteKongPolicy($policy_id);
      $this->messenger()->addMessage($this->t('Policy deleted succesfully'));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in deleting policy. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }

    return new RedirectResponse(Url::fromRoute('kong_api_publisher.policy_management')->toString());
  }

  public function deleteService() {
    $service_id = \Drupal::routeMatch()->getParameter('service_id');
    try {
      Service::deleteKongService($service_id);
      $this->messenger()->addMessage($this->t('Service deleted succesfully'));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Error in deleting service. <p>%msg</p>', [
        '%msg' => $e->getMessage(),
      ]));

      Utils::logger()->error($e->getMessage());
    }

    return new RedirectResponse(Url::fromRoute('kong_api_publisher.services')->toString());
  }

}
