## INSTALLATION ##

These are the steps you need to take in order to use this software.

 1. Install dependencies with composer. Run ```composer require 'adyax_support/monitoring_tool_client:^9.1.0'```
 2. Edit settings.php to configure the server, add these settings there:
```php
$settings['monitoring_tool'] = [
  'base_url' => 'http://example.com',
  'options' => [
  'auth' => ['username', 'password'],
    'headers' => [
      'X-Foo' => 'overwrite',
      'verify' => true,
    ],
  ],
];
```
More information about options you can read
here: http://docs.guzzlephp.org/en/stable/request-options.html.

Then configure the settings on the page: /admin/config/services/monitoring-tool-client.
