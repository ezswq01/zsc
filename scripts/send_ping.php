<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

try {
    $mqtt = MQTT::connection(); // Using the default connection configured in mqtt-client.php

    $mqtt->publish('izisec/broadcastbymcc', 'mainpingbymcc', 0);
    $mqtt->publish('izisec/broadcastbymcc', 'getinactivehour', 0);
    $mqtt->publish('izisec/broadcastbymcc', 'getactivehour', 0);

    $mqtt->disconnect();
} catch (\PhpMqtt\Client\Exceptions\MqttClientException $e) {
    error_log('Could not connect to MQTT broker: ' . $e->getMessage());
}

