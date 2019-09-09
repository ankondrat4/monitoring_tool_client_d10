<?php

namespace Drupal\monitoring_tool_client\Service;

/**
 * Interface ClientApiServiceInterface.
 */
interface ClientApiServiceInterface {

  /**
   * Will do HTTP request with list of the modules.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function sendModules();

}
