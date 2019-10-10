<?php

namespace Drupal\monitoring_tool_client\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\monitoring_tool_client\Service\ServerConnectorServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestConnectionController.
 */
class TestConnectionController implements ContainerInjectionInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * TestConnectionController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Request $request) {
    $this->configFactory = $config_factory;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * Test_connection route callback.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Http response.
   */
  public function testConnection() {
    return new Response(NULL, Response::HTTP_OK);
  }

  /**
   * Check access by header token.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess() {
    $config = $this->configFactory->get('monitoring_tool_client.settings');
    $secure_token = $this->request->headers->has(ServerConnectorServiceInterface::MONITORING_TOOL_ACCESS_HEADER)
      ? $this->request->headers->get(ServerConnectorServiceInterface::MONITORING_TOOL_ACCESS_HEADER)
      : '';

    return AccessResult::forbiddenIf(
      empty($secure_token) ||
      $secure_token !== $config->get('secure_token')
    );
  }

}
