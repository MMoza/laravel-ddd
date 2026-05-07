<?php

return [
    'domains_path' => app_path('Domains'),
    'application_path' => app_path('Application'),
    'infrastructure_path' => app_path('Infrastructure'),
    'support_path' => app_path('Support'),
    'providers_path' => app_path('Providers'),

    'default_namespace' => 'App\\Domains',

    'stubs_path' => __DIR__ . '/../src/stubs',

    'generate_tests' => true,

    'routes_path' => base_path('routes/domains'),
];