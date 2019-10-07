<?php

/**
 * @file
 * Administrative functions for the Monitoring tool client.
 */

/**
 * Will return the settings_form.
 *
 * @param array $form
 *   Drupal form.
 * @param array $form_state
 *   Drupal form state.
 *
 * @return array
 *   Drupal form.
 */
function monitoring_tool_client_settings_form(array $form, array &$form_state) {
  $config = variable_get(MONITORING_TOOL_CLIENT_SETTINGS);

  form_load_include($form_state, 'inc', 'monitoring_tool_client', 'admin');

  $form['general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General'),
  );

  $form['general']['webhook'] = array(
    '#type' => 'checkbox',
    '#parents' => array('webhook'),
    '#title' => t('Wait for webhook'),
    '#description' => t('<b>Important!</b> The report will be sent after triggering via webhook from central server. <br> Uncheck the checkbox in case if the project is Application or don\'t have public www address.'),
    '#default_value' => $config['webhook'],
  );

  $form['general']['report_interval'] = array(
    '#type' => 'select',
    '#parents' => array('report_interval'),
    '#title' => t('Send the report'),
    '#description' => t('How often need to send the report to server.'),
    '#default_value' => $config['report_interval'],
    '#options' => array(
      0 => t('Each cron execution'),
      3600 => t('1 hour'),
      10800 => t('3 hours'),
      21600 => t('6 hours'),
      32400 => t('9 hours'),
      43200 => t('12 hours'),
      86400 => t('1 day'),
    ),
    '#states' => array(
      'visible' => array(
        ':input[name="webhook"]' => array(
          'checked' => FALSE,
        ),
      ),
    ),
  );

  $form['security'] = array(
    '#type' => 'fieldset',
    '#title' => t('Security'),
  );

  $form['security']['project_id'] = array(
    '#type' => 'textfield',
    '#parents' => array('project_id'),
    '#title' => t('Project ID'),
    '#description' => t('This is hash ID you can take from central server system.<br> On the project edit page.'),
    '#default_value' => $config['project_id'],
    '#attributes' => array(
      'autocomplete' => 'off',
    ),
  );

  $form['security']['token'] = array(
    '#type' => 'textfield',
    '#parents' => array('secure_token'),
    '#title' => t('Secure token'),
    '#description' => t('This token you can take from central server system.<br> On the project edit page.'),
    '#default_value' => $config['secure_token'],
    '#size' => 120,
    '#attributes' => array(
      'autocomplete' => 'off',
    ),
  );

  $form['skip_checking_updates'] = array(
    '#type' => 'fieldset',
    '#title' => t('Skip of checking updates'),
    '#description' => t('All selected modules will be ignored on time checking updates. <br> This modules are modules with some additional custom solutions or have a lot of patches.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $modules_options = array();

  foreach (monitoring_tool_client_get_modules() as $machine_name => $info) {
    $modules_options[$machine_name] = $info['name'];
  }

  $form['skip_checking_updates']['skip_updates'] = array(
    '#type' => 'checkboxes',
    '#parents' => array('skip_updates'),
    '#title' => t('List of installed contrib modules'),
    '#description' => t('Select the modules that should be ignored on time checking updates.'),
    '#default_value' => $config['skip_updates'],
    '#options' => $modules_options,
  );

  $form['actions'] = array(
    '#type' => 'actions',
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Apply'),
  );

  return $form;
}

/**
 * Callback of settings_form.
 *
 * @param array $form
 *   Drupal form.
 * @param array $form_state
 *   Drupal form state.
 */
function monitoring_tool_client_settings_form_submit(array $form, array $form_state) {
  form_state_values_clean($form_state);
  variable_set(MONITORING_TOOL_CLIENT_SETTINGS, $form_state['values']);
  drupal_set_message(t('The configuration options have been saved.'));
}
