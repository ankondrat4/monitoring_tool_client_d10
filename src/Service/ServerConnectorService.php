<?php

namespace Drupal\monitoring_tool_client\Service;

use Drupal\Core\Site\Settings;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * Class ServerConnectorService.
 */
class ServerConnectorService implements ServerConnectorServiceInterface {

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * @var array
   */
  protected $settings;

  /**
   * ServerConnectorService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
    $this->settings = Settings::get('monitoring_tool', []) + ['options' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function send(array $data) {
    if (!empty($this->settings['base_url'])) {
      $url = rtrim($this->settings['base_url'], '/') . static::MONITORING_TOOL_ENDPOINT;
      $options = [
        RequestOptions::JSON => $data,
      ] + $this->settings['options'] + [
        RequestOptions::ALLOW_REDIRECTS => TRUE,
        RequestOptions::VERIFY => FALSE,
        RequestOptions::HTTP_ERRORS => FALSE,
      ];

      $this->httpClient->request('POST', $url, $options);
    }
  }

}
