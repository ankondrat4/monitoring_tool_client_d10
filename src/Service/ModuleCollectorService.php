<?php

namespace Drupal\monitoring_tool_client\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\monitoring_tool_client\Service\DatabaseServiceInterface;

/**
 * Class ModuleCollectorService.
 */
class ModuleCollectorService implements ModuleCollectorServiceInterface {

  /**
   * Configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The list of Drupal core and modules database updates.
   *
   * @var \Drupal\monitoring_tool_client\Service\DatabaseServiceInterface
   */
  protected $database;

  /**
   * The update registry service.
   *
   * @var \Drupal\Core\Update\UpdateHookRegistry
   */
  protected $updateRegistry;

  /**
   * CollectModulesService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\monitoring_tool_client\Service\DatabaseServiceInterface $database
   *   The list of Drupal core and modules database updates.
   * @param \Drupal\Core\Update\UpdateHookRegistry $update_registry
   *   The update registry service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    DatabaseServiceInterface $database
  ) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->database = $database;
    $this->updateRegistry = $update_registry;
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    $result = [];
    $configuration = $this->configFactory->get('monitoring_tool_client.settings');
    $skip_list = $configuration->get('skip_updates');
    /** @var \Drupal\Core\Extension\Extension[] $module_list */
    $module_list = array_filter(
      \Drupal::service("extension.list.module")->getList(),
      [static::class, 'filterContribModules']
    );

    $db_updates = $this->database->getUpdates();

    $result['drupal'] = [
      'machine_name' => 'drupal',
      'name' => 'Drupal core',
      'core' => \Drupal::CORE_COMPATIBILITY,
      'status' => TRUE,
      'version' => \Drupal::VERSION,
      'skip_updates' => FALSE,
      'database_updates' => (isset($db_updates['system'])) ? TRUE : FALSE,
    ];

    foreach ($module_list as $module_name => $module) {
      $info = isset($module->info) ? $module->info : [];
      $result[$module_name] = [
        'machine_name' => $module->getName(),
        'name' => $info['name'],
        'core' => \Drupal::CORE_COMPATIBILITY,
        'version' => $info['version'],
        'status' => $this->moduleHandler->moduleExists($module->getName()),
        'skip_updates' => !empty($skip_list[$module->getName()]),
        'database_updates' => (isset($db_updates[$module->getName()])) ? TRUE : FALSE,
      ];
    }

    return $result;
  }

  /**
   * Will filter the custom and child modules.
   *
   * @param \Drupal\Core\Extension\Extension $module
   *   Module item.
   *
   * @return bool
   *   Filter or not.
   */
  public static function filterContribModules(Extension $module) {
    $info = isset($module->info) ? $module->info : [];

    if (
      isset($info['project']) &&
      // Will ignore exaction not modules.
      $module->getType() === 'module' &&
      // Will ignore the drupal core modules.
      $info['project'] !== 'drupal' &&
      // Will ignore the modules that are located in the same folder.
      $module->getName() === $info['project'] &&
      // Will ignore the child modules.
      basename(dirname($module->getPathname())) === $info['project']
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check pending database updates on the site.
   *
   * @return bool
   *   Has pending database updates or not.
   */
  public function pendingDBUpdates() {
    // Check installed modules.
    $has_pending_updates = FALSE;
    foreach ($this->moduleHandler->getModuleList() as $module => $filename) {
      $updates = $this->updateRegistry->getAvailableUpdates($module);
      if ($updates !== FALSE && !empty($updates)) {
        $default = $this->updateHookRegistry->getInstalledVersion($module);
        if (max($updates) > $default) {
          $has_pending_updates = TRUE;
          break;
        }
      }
    }

    return $has_pending_updates;
  }

}
