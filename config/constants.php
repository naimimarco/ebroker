<?php


define('PERMISSION_ERROR_MSG', 'You are not authorize to operate on the module ');
define('JWT_SECRET_KEY', '404D635166546A576E5A7234753778214125442A472D4B614E645267556B5870');

defined('PAYPAL_SANDBOX_MODE') or define('PAYPAL_SANDBOX_MODE', true);
define('sandbox', true);
//Sandbox
defined('business') or define('business', 'sb-uefcv23946367@business.example.com');

//Live
// defined('PAYPAL_LIVE_BUSINESS_EMAIL') or define('PAYPAL_LIVE_BUSINESS_EMAIL', '');
// defined('PAYPAL_CURRENCY') or define('PAYPAL_CURRENCY', 'USD');

return [
    'CACHE' => [
        'SYSTEM' => [
            'DEFAULT_LANGUAGE' => 'default_language',
            'SETTINGS' => 'systemSettings'
        ],
    ],
    'RESPONSE_CODE' => [
        'EXCEPTION_ERROR' => 500,
        'SUCCESS' => 200,
        'VALIDATION_ERROR' => 400
    ],
    'FEATURES' => [
        'PROPERTY_LIST'                 => 'Property List',
        'PROPERTY_FEATURE'              => 'Property Feature List',
        'PROJECT_LIST'                  => 'Project List',
        'PROJECT_FEATURE'               => 'Project Feature List',
        'MORTGAGE_CALCULATOR_DETAIL'    => 'Mortgage Calculator Detail Access',
        'PREMIUM_PROPERTIES'            => 'Premium Properties Access',
        'PROJECT_ACCESS'                => 'Project List Access'
    ],

    'HOMEPAGE_SECTION_TYPES' => [
        'AGENTS_LIST_SECTION'               => ['TYPE' => 'agents_list_section',            'TITLE' => 'Agent Sections List'],
        'ARTICLES_SECTION'                  => ['TYPE' => 'articles_section',               'TITLE' => 'Article Sections List'],
        'CATEGORIES_SECTION'                => ['TYPE' => 'categories_section',             'TITLE' => 'Category Sections List'],
        'FAQS_SECTION'                      => ['TYPE' => 'faqs_section',                   'TITLE' => 'FAQ Sections List'],
        'FEATURED_PROPERTIES_SECTION'       => ['TYPE' => 'featured_properties_section',    'TITLE' => 'Featured Properties Sections List'],
        'FEATURED_PROJECTS_SECTION'         => ['TYPE' => 'featured_projects_section',      'TITLE' => 'Featured Projects Sections List'],
        'MOST_LIKED_PROPERTIES_SECTION'     => ['TYPE' => 'most_liked_properties_section',  'TITLE' => 'Most Liked Properties Sections List'],
        'MOST_VIEWED_PROPERTIES_SECTION'    => ['TYPE' => 'most_viewed_properties_section', 'TITLE' => 'Most Viewed Properties Sections List'],
        'NEARBY_PROPERTIES_SECTION'         => ['TYPE' => 'nearby_properties_section',      'TITLE' => 'Nearby Properties Sections List'],
        'PROJECTS_SECTION'                  => ['TYPE' => 'projects_section',               'TITLE' => 'Project Sections List'],
        'PREMIUM_PROPERTIES_SECTION'        => ['TYPE' => 'premium_properties_section',     'TITLE' => 'Premium Properties Sections List'],
        'USER_RECOMMENDATIONS_SECTION'      => ['TYPE' => 'user_recommendations_section',   'TITLE' => 'User Recommendations Sections List'],
        'PROPERTIES_BY_CITIES_SECTION'      => ['TYPE' => 'properties_by_cities_section',   'TITLE' => 'Properties by Cities Sections List']
    ]
];