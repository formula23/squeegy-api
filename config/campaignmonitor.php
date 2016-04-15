<?php

return [

    'api_key' => env('CAMPAIGNMONITOR_API_KEY'),

    'client_id' => env('CAMPAIGNMONITOR_CLIENT_ID'),

    'master_list_id' => env('CAMPAIGNMONITOR_MASTER_LIST_ID'),

    'template_ids' => [
        'receipt' => env('CM_RECEIPT_ID'),
        'pw_reset' => env('CM_PW_RESET_ID'),
    ]
    
];
