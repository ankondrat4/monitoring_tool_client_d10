<?php

namespace Drupal\monitoring_tool_client\Functional;

use Drupal\Core\Access\AccessResult;
use Drupal\monitoring_tool_client\Service\ServerConnectorServiceInterface;

/**
 * Trait AccessCheckTrait.
 */
trait AccessCheckTrait {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Checking of the access by header Token.
   *
   * @param string $project_id
   *   The project ID from Monitoring tool Server.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess($project_id) {
    $config = $this->getConfigFactory()->get('monitoring_tool_client.settings');
    $secure_token = $this->getCurrentRequest()->headers->get(ServerConnectorServiceInterface::MONITORING_TOOL_ACCESS_HEADER);

    return AccessResult::allowedIf(
      $config->get('webhook') === TRUE &&
      !empty($project_id) &&
      !empty($secure_token) &&
      $project_id === $config->get('project_id') &&
      $secure_token === $config->get('secure_token')
    );
  }

  /**
   * Will return the Request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Http request.
   */
  protected function getCurrentRequest() {
    return $this->getRequestStack()->getCurrentRequest();
  }

  /**
   * Will return the request stack.
   *
   * @return \Symfony\Component\HttpFoundation\RequestStack
   *   The request stack.
   */
  protected function getRequestStack() {
    if (empty($this->requestStack)) {
      $this->requestStack = \Drupal::service('request_stack');
    }

    return $this->requestStack;
  }

  /**
   * Will return the config factory.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   */
  protected function getConfigFactory() {
    if (empty($this->configFactory)) {
      $this->configFactory = \Drupal::service('config.factory');
    }

    return $this->configFactory;
  }

}
