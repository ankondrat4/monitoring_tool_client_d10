<?php

namespace Drupal\monitoring_tool_client\Service;

/**
 * Interface ServerConnectorServiceInterface.
 */
interface ServerConnectorServiceInterface {

  /**
   * Endpoint of the Monitoring Tool server.
   */
  const MONITORING_TOOL_ENDPOINT = '/monitoring-tool/input';

  /**
   * Will send data to the common Monitoring Tool server.
   *
   * @param array $data
   *   Data for sending to Monitoring Tool server.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function send(array $data);

}
