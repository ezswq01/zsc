<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class MqttSignalListener extends Command
{
    protected $signature = 'mqtt-signal';
    protected $description = 'Listen for MQTT signal messages';

    // Allowed payloads for different topics
    protected $topic1Payloads = [
        'tempsnok',
        'vibsnok',
        'magsnok',
        'motsnok',
        'albuzaoffbymcc',
	'doorsnok'
    ];

    protected $topic2Payloads = [
        'lbatsnok',
        'acvsnok',
        'albuzrlnok',
        'albuzwoffbymcc',
	'doorsopsnok',
	'swsnok',
	'wcamnok',
	'wbuznok',
	'wcamimgnok'
    ];

    public function handle()
    {
        $mqtt = MQTT::connection();

        // Hard-coded topics
        $mccTopic = 'mcc/#';
        $izisecTopic = 'izisec/#';

        // Subscribe to mcc/# and handle incoming messages
        $mqtt->subscribe($mccTopic, function (string $topic, string $payload) use ($mqtt) {
            $this->handleMessage($mqtt, $payload);
        }, 0);

        // Subscribe to izisec/# and handle incoming messages
        $mqtt->subscribe($izisecTopic, function (string $topic, string $payload) use ($mqtt) {
            $this->handleMessage($mqtt, $payload);
        }, 0);

        // Keep the script running to listen for messages
        while ($mqtt->loop()) {
            // Additional logic can be added here if needed
        }

        $mqtt->disconnect();
    }

    protected function handleMessage($mqtt, $payload)
    {
        // Check if the payload is in the first topic's allowed list
        if (in_array($payload, $this->topic1Payloads)) {
            $modifiedPayload = $payload . '_sig';
            try {
                $mqtt->publish('izisec/as/as/as/cs/pub', $modifiedPayload, 0);
                $this->info("Published to izisec/as/as/as/cs/pub: {$modifiedPayload}");
            } catch (\Exception $e) {
                $this->error("Failed to publish to cs/pub: " . $e->getMessage());
            }
        }
        // Check if the payload is in the second topic's allowed list
        elseif (in_array($payload, $this->topic2Payloads)) {
            $modifiedPayload = $payload . '_sig';
            try {
                $mqtt->publish('izisec/as/as/as/ws/pub', $modifiedPayload, 0);
                $this->info("Published to izisec/as/as/as/ws/pub: {$modifiedPayload}");
            } catch (\Exception $e) {
                $this->error("Failed to publish to ws/sub: " . $e->getMessage());
            }
        } else {
            $this->info("Payload '{$payload}' not allowed.");
        }
    }
}
