<?php

/**
 * @file
 * WebHook functionality for the Monitoring tool client.
 */

/**
 * Callback of the hook_menu for webhook.
 *
 * @param string $project_id
 *   Project ID from the Monitoring tool - Server.
 */
function monitoring_tool_client_webhook($project_id) {
  $config = variable_get(MONITORING_TOOL_CLIENT_SETTINGS);
  $secure_token_key = 'HTTP_' . strtoupper(str_replace('-', '_', MONITORING_TOOL_CLIENT_SECURE_TOKEN));
  $secure_token = isset($_SERVER[$secure_token_key]) ? $_SERVER[$secure_token_key] : '';

  if (
    $config['project_id'] === '' ||
    $config['secure_token'] === '' ||
    $project_id !== $config['project_id'] ||
    $secure_token !== $config['secure_token'] ||
    !$config['webhook']
  ) {
    drupal_access_denied();
    drupal_exit();
  }

  monitoring_tool_client_send_modules();

  drupal_add_http_header('Status', '204 No Content', TRUE);
  drupal_exit();
}
