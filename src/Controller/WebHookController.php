<?php

namespace Drupal\monitoring_tool_client\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\monitoring_tool_client\Service\ClientApiServiceInterface;
use Drupal\monitoring_tool_client\Service\ServerConnectorServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class WebHookController.
 */
class WebHookController implements ContainerInjectionInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Monitoring tool client API.
   *
   * @var \Drupal\monitoring_tool_client\Service\ClientApiServiceInterface
   */
  protected $clientApi;

  /**
   * WebHookController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\monitoring_tool_client\Service\ClientApiServiceInterface $client_api
   *   HTTP Guzzle client.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RequestStack $request_stack,
    ClientApiServiceInterface $client_api
  ) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->clientApi = $client_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('monitoring_tool_client.client_api')
    );
  }

  /**
   * WebHook route callback.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Http response.
   */
  public function sendModules() {
    $this->checkAccess();

    try {
      $this->clientApi->sendModules();
    }
    catch (GuzzleException $exception) {
      return new Response(NULL, Response::HTTP_SERVICE_UNAVAILABLE);
    }
    catch (HttpExceptionInterface $exception) {
      return new Response(NULL, $exception->getStatusCode(), $exception->getHeaders());
    }

    return new Response(NULL, Response::HTTP_NO_CONTENT);
  }

  /**
   * Checking of the access by header Token.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  private function checkAccess() {
    $request = $this->request();
    $config = $this->configFactory->get('monitoring_tool_client.settings');

    if (
        $config->get('use_webhook') === FALSE ||
        $request->headers->has(ServerConnectorServiceInterface::MONITORING_TOOL_ACCESS_HEADER) === FALSE ||
        $config->get('secure_token') !== $request->headers->get(ServerConnectorServiceInterface::MONITORING_TOOL_ACCESS_HEADER)
    ) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Will return the Request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   Http request.
   */
  private function request() {
    $request = $this->requestStack->getCurrentRequest();

    if ($request === NULL) {
      throw new BadRequestHttpException();
    }

    return $request;
  }

}