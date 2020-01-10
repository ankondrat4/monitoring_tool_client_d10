<?php

namespace Drupal\monitoring_tool_client\Service;

/**
 * Interface DatabaseServiceInterface.
 */
interface DatabaseServiceInterface {

  /**
   * Get the list of Drupal core and modules updates.
   *
   * @return array
   *   The list of Drupal core and modules updates.
   */
  public function getUpdates();

}
