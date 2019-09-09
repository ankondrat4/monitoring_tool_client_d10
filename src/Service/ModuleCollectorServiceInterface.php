<?php

namespace Drupal\monitoring_tool_client\Service;

/**
 * Interface ModuleCollectorServiceInterface.
 */
interface ModuleCollectorServiceInterface {

  /**
   * Cache ID.
   */
  const CACHE_CID = 'monitoring_tool_client.module_list';

  /**
   * Cache expire time.
   *
   * 21600 - it is 6h.
   */
  const CACHE_EXPIRE_TIME = 21600;

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
