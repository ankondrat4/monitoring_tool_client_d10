<?php

namespace Drupal\monitoring_tool_client\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;

/**
 * Class ModuleCollectorService.
 */
class ModuleCollectorService implements ModuleCollectorServiceInterface {

  /**
   * Module extensions service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * Configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * CollectModulesService constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   Module extensions service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache service.
   */
  public function __construct(
    ModuleExtensionList $module_extension_list,
    ConfigFactoryInterface $config_factory,
    CacheBackendInterface $cache
  ) {
    $this->moduleExtensionList = $module_extension_list;
    $this->configFactory = $config_factory;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->cache->delete(static::CACHE_CID);
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    $cache = $this->cache->get(static::CACHE_CID);

    if ($cache) {
      return $cache->data;
    }

    $result = $this->collectModules();

    $this->cache->set(static::CACHE_CID, $result, static::CACHE_EXPIRE_TIME);

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
  public static function filterContribModules($module) {
    if (
      // Will ignore exaction not modules.
      $module->getType() === 'module' &&
      // Will ignore the drupal core modules.
      $module->info['project'] !== 'drupal' &&
      // Will ignore the modules that are located in the same folder.
      $module->getName() === $module->info['project'] &&
      // Will ignore the child modules.
      basename(dirname($module->getPathname())) === $module->info['project']
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Will collect all modules and Drupal core.
   *
   * @return array
   *   Modules and core.
   */
  protected function collectModules() {
    $result = [];
    $configuration = $this->configFactory->get('monitoring_tool_client.settings');
    $weak_list = $configuration->get('weak_list');
    /** @var \Drupal\Core\Extension\Extension[] $module_list */
    $module_list = array_filter(
      $this->moduleExtensionList->reset()->getList(),
      [static::class, 'filterContribModules']
    );

    $result['drupal'] = [
      'machine_name' => 'drupal',
      'name' => 'Drupal core',
      'version' => \Drupal::VERSION,
      'weak_status' => !empty($weak_list['drupal']),
    ];

    foreach ($module_list as $module_name => $module) {
      $result[$module_name] = [
        'machine_name' => $module->getName(),
        'name' => $module->info['name'],
        'version' => $module->info['version'],
        'weak_status' => !empty($weak_list[$module->getName()]),
      ];
    }

    return $result;
  }

}
