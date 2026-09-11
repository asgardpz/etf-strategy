<?php
return [
    'account_amount' => (float) env('ETF_ACCOUNT_AMOUNT', 800000),
    'refresh_minutes' => (int) env('ETF_REFRESH_MINUTES', 5),
    'twse' => [
        'base_url' => env('TWSE_BASE_URL', 'https://www.twse.com.tw'),
        'openapi_url' => env('TWSE_OPENAPI_URL', 'https://openapi.twse.com.tw/v1'),
        'quote_url' => env('TWSE_QUOTE_URL', 'https://mis.twse.com.tw/stock/api/getStockInfo.jsp'),
        'timeout' => (int) env('TWSE_TIMEOUT', 20),
        'user_agent' => env('TWSE_USER_AGENT', 'ETFStrategy/1.0'),
    ],
];
