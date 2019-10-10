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

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General'),
    ];

    $form['general']['webhook'] = [
      '#type' => 'checkbox',
      '#parents' => ['webhook'],
      '#title' => $this->t('Wait for webhook'),
      '#description' => $this->t('<b>Important!</b> The report will be sent after triggering via webhook from central server. <br> Uncheck the checkbox in case if the project is Application or don\'t have public www address.'),
      '#default_value' => $config->get('webhook'),
    ];

    $form['general']['report_interval'] = [
      '#type' => 'select',
      '#parents' => ['report_interval'],
      '#title' => $this->t('Send the report'),
      '#description' => $this->t('How often need to send the report to server.'),
      '#default_value' => $config->get('report_interval'),
      '#options' => [
        0 => $this->t('Each cron execution'),
        3600 => $this->t('1 hour'),
        10800 => $this->t('3 hours'),
        21600 => $this->t('6 hours'),
        32400 => $this->t('9 hours'),
        43200 => $this->t('12 hours'),
        86400 => $this->t('1 day'),
      ],
      '#states' => [
        'visible' => [
          ':input[name="webhook"]' => [
            'checked' => FALSE,
          ],
        ],
      ],
    ];

    $form['security'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Security'),
    ];

    $form['security']['project_id'] = [
      '#type' => 'textfield',
      '#parents' => ['project_id'],
      '#title' => $this->t('Project ID'),
      '#description' => $this->t('This is hash ID you can take from central server system.<br> On the project edit page.'),
      '#default_value' => $config->get('project_id'),
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];

    $form['security']['token'] = [
      '#type' => 'textfield',
      '#parents' => ['secure_token'],
      '#title' => $this->t('Secure token'),
      '#description' => $this->t('This token you can take from central server system.<br> On the project edit page.'),
      '#default_value' => $config->get('secure_token'),
      '#size' => 120,
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];

    $form['skip_checking_updates'] = [
      '#type' => 'details',
      '#title' => $this->t('Skip of checking updates'),
      '#description' => $this->t('All selected modules will be ignored on time checking updates. <br> This modules are modules with some additional custom solutions or have a lot of patches.'),
      '#open' => FALSE,
    ];

    $skip_updates_options = array_column(
      $this->moduleCollector->getModules(),
      'name',
      'machine_name'
    );

    unset($skip_updates_options['drupal']);

    $form['skip_checking_updates']['skip_updates'] = [
      '#type' => 'checkboxes',
      '#parents' => ['skip_updates'],
      '#title' => $this->t('List of installed contrib modules'),
      '#description' => $this->t('Select the modules that should be ignored on time checking updates.'),
      '#default_value' => $config->get('skip_updates'),
      '#options' => $skip_updates_options,
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
    $values['skip_updates'] = array_filter($values['skip_updates']);

    $this->configFactory()
      ->getEditable('monitoring_tool_client.settings')
      ->setData($values)
      ->save();

    $this->messenger()->addStatus($this->t('The changes have been saved.'));
  }

}
