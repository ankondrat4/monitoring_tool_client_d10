<?php

namespace Drupal\monitoring_tool_client\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\Extension;
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
   * CollectModulesService constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   Module extensions service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration manager.
   */
  public function __construct(
    ModuleExtensionList $module_extension_list,
    ConfigFactoryInterface $config_factory
  ) {
    $this->moduleExtensionList = $module_extension_list;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->moduleExtensionList->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function getModules() {
    $result = [];
    $configuration = $this->configFactory->get('monitoring_tool_client.settings');
    $weak_list = $configuration->get('weak_list');
    /** @var \Drupal\Core\Extension\Extension[] $module_list */
    $module_list = array_filter(
      $this->moduleExtensionList->getList(),
      [static::class, 'filterContribModules']
    );

    $result['drupal'] = [
      'machine_name' => 'drupal',
      'name' => 'Drupal core',
      'weak_status' => !empty($weak_list['drupal']),
    ] + static::parseVersion(\Drupal::VERSION);

    foreach ($module_list as $module_name => $module) {
      $info = isset($module->info) ? $module->info : [];
      $result[$module_name] = [
        'machine_name' => $module->getName(),
        'name' => $info['name'],
        'weak_status' => !empty($weak_list[$module->getName()]),
      ] + static::parseVersion($info['version']);
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
   * Will parse the version of modules or drupal core.
   *
   * @param string $version
   *   Version of a module or Drupal core.
   *
   * @return array
   *   Array of major, minor, patch and extra values.
   */
  protected static function parseVersion($version) {
    $output = [
      'core' => \Drupal::CORE_COMPATIBILITY,
      'version_major' => NULL,
      'version_minor' => NULL,
      'version_patch' => NULL,
      'version_extra' => NULL,
    ];

    if (preg_match('/^(?:\d+\.x-)?(\d+)(?:\.(\d+))?\.(\d+|x)(?:-(\w+))?$/i', $version, $matches)) {
      array_shift($matches);
      list(
        $output['version_major'],
        $output['version_minor'],
        $output['version_patch'],
        $output['version_extra']
      ) = $matches + [1 => NULL, 2 => NULL, 3 => NULL, 4 => NULL];
    }

    return $output;
  }

}
