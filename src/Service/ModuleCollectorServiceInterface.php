<?php

namespace Drupal\monitoring_tool_client\Service;

/**
 * Interface ModuleCollectorServiceInterface.
 */
interface ModuleCollectorServiceInterface {

  /**
   * Will rebuild list of modules.
   */
  public function resetCache();

  /**
   * Will return list of all modules and drupal core.
   *
   * @return array
   *   List of modules and Drupal.
   */
  public function getModules();

}
