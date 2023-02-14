<?php

namespace Drupal\monitoring_tool_client\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class DatabaseService.
 */
class DatabaseService implements DatabaseServiceInterface {

  /**
   * Configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CollectModulesService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getUpdates() {
    $updates = [];
    $settings = $this->configFactory->get('monitoring_tool_client.settings');
    $skip = $settings->get('skip_drupal_database_update');

    if (!$skip) {
      $this->loadIncludes();
      $updates = update_get_update_list();
    }

    return $updates;
  }

  /**
   * Loads a module update and install include files.
   */
  private function loadIncludes() {
    require_once DRUPAL_ROOT . '/core/includes/update.inc';
    require_once DRUPAL_ROOT . '/core/includes/install.inc';
    drupal_load_updates();
  }

}
