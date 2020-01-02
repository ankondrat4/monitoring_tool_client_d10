## INSTALLATION ##

These are the steps you need to take in order to use this software.

 1. Install dependencies with composer. Run ```composer require 'adyax_support/monitoring_tool_client:^7.1.0'```
 2. Edit settings.php to configure the server, add these settings there:
```php
$conf['monitoring_tool'] = [
  'base_url' => 'http://example.com',
  'headers' => [
    'Authorization' => 'Basic XXXXXXXX==',
  ],
];
```
More information about options you can read
here: https://api.drupal.org/api/drupal/includes!common.inc/function/drupal_http_request/7.x

Then configure the settings on the page: /admin/config/services/monitoring-tool-client.
