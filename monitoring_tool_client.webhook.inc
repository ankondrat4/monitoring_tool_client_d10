<?php

/**
 * @file
 * WebHook functionality for the Monitoring tool client.
 */

/**
 * Callback of the hook_menu for webhook.
 */
function monitoring_tool_client_webhook() {
  $secure_token_key = 'HTTP_' . strtoupper(str_replace('-', '_', MONITORING_TOOL_CLIENT_SECURE_TOKEN));
  $config = variable_get(MONITORING_TOOL_CLIENT_SETTINGS);
  $secure_token = isset($_SERVER[$secure_token_key]) ? $_SERVER[$secure_token_key] : '';

  if (!$config['use_webhook'] || $secure_token !== $config['secure_token']) {
    drupal_access_denied();
  }

  monitoring_tool_client_send_modules();

  drupal_add_http_header('Status', '204 No Content', TRUE);
  drupal_exit();
}
