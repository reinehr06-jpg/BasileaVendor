<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),
    
    // The release version of your application
    // Example with dynamic git hash: trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD'))
    'release' => env('SENTRY_RELEASE'),
    
    // When left empty or `null` the Laravel environment will be used
    'environment' => env('SENTRY_ENVIRONMENT'),
    
    'breadcrumbs' => [
        // Capture Laravel logs in breadcrumbs
        'logs' => true,
        
        // Capture SQL queries in breadcrumbs
        'sql_queries' => true,
        
        // Capture bindings on SQL queries logged in breadcrumbs
        'sql_bindings' => true,
        
        // Capture queue job information in breadcrumbs
        'queue_info' => true,
        
        // Capture command information in breadcrumbs
        'command_info' => true,
    ],
    
    'tracing' => [
        // Trace queue jobs as their own transactions
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_JOBS', false),
        
        // Trace queue jobs for the transactions that dispatched them
        'queue_jobs' => true,
        
        // Trace SQL queries
        'sql_queries' => true,
        
        // Trace views
        'views' => true,
        
        // Indicates if the tracing integrations supplied by Sentry should be loaded
        'default_integrations' => true,
    ],
    
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
    
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),
];
