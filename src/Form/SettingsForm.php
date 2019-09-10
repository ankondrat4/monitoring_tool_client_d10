<?php

namespace Drupal\monitoring_tool_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\monitoring_tool_client\Service\ModuleCollectorServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends FormBase {

  /**
   * Service module collector.
   *
   * @var \Drupal\monitoring_tool_client\Service\ModuleCollectorServiceInterface
   */
  protected $moduleCollector;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\monitoring_tool_client\Service\ModuleCollectorServiceInterface $module_collector
   *   Service module collector.
   */
  public function __construct(ModuleCollectorServiceInterface $module_collector) {
    $this->moduleCollector = $module_collector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('monitoring_tool_client.module_collector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'monitoring_tool_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('monitoring_tool_client.settings');

    $form['security'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Security'),
    ];

    $form['security']['token'] = [
      '#type' => 'textfield',
      '#parents' => ['secure_token'],
      '#title' => $this->t('Secure token'),
      '#default_value' => $config->get('secure_token'),
      '#size' => 120,
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Common'),
    ];

    $form['general']['use_webhook'] = [
      '#type' => 'checkbox',
      '#parents' => ['use_webhook'],
      '#title' => $this->t('Use WebHook'),
      '#default_value' => $config->get('use_webhook'),
    ];

    $form['weak'] = [
      '#type' => 'details',
      '#title' => $this->t('Weak list'),
      '#open' => FALSE,
    ];

    $form['weak']['modules'] = [
      '#type' => 'checkboxes',
      '#parents' => ['weak_list'],
      '#title' => $this->t('List of week modules'),
      '#default_value' => $config->get('weak_list'),
      '#options' => array_column(
        $this->moduleCollector->getModules(),
        'name',
        'machine_name'
      ),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    $values = $form_state->getValues();
    $values['weak_list'] = array_filter($values['weak_list']);

    $this->configFactory()
      ->getEditable('monitoring_tool_client.settings')
      ->setData($values)
      ->save();

    $this->messenger()->addStatus($this->t('The changes have been saved.'));
  }

}
