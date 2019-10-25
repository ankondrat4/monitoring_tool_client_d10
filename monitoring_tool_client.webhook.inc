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
  monitoring_tool_client_send_modules();

  drupal_add_http_header('Status', '204 No Content', TRUE);
  drupal_exit();
}
