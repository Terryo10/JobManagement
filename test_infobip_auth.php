<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\InfobipClient;

$key  = config('services.infobip.api_key');
$base = config('services.infobip.base_url');

echo "API Key (first 20 chars): " . substr($key, 0, 20) . "..." . PHP_EOL;
echo "Base URL: " . $base . PHP_EOL . PHP_EOL;

// Test direct HTTP call (bypassing the singleton)
$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Authorization' => 'App ' . $key,
    'Content-Type'  => 'application/json',
    'Accept'        => 'application/json',
])->post($base . '/sms/2/text/advanced', [
    'messages' => [[
        'from'         => config('services.infobip.sms_sender'),
        'destinations' => [['to' => '+263782813199']],
        'text'         => 'Direct HTTP test - ' . date('H:i:s'),
    ]],
]);

echo "Direct HTTP status: " . $response->status() . PHP_EOL;
echo "Response: " . $response->body() . PHP_EOL . PHP_EOL;

// Now test via the singleton
echo "Testing via InfobipClient singleton..." . PHP_EOL;
try {
    $client = app(InfobipClient::class);
    $result = $client->sendSms('+263782813199', 'Via InfobipClient singleton - ' . date('H:i:s'));
    echo "Singleton status: OK" . PHP_EOL;
    echo "Message ID: " . ($result['messages'][0]['messageId'] ?? 'none') . PHP_EOL;
} catch (\Throwable $e) {
    echo "Singleton error: " . $e->getMessage() . PHP_EOL;
}
