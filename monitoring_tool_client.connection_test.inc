<?php

/**
 * @file
 * Connection test functionality for the Monitoring tool client.
 */

/**
 * Callback of the hook_menu for connection test.
 */
function monitoring_tool_client_connection_test() {
  drupal_add_http_header('Status', '204 No Content', TRUE);
  drupal_exit();
}
