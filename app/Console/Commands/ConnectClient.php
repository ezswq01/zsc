<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use PhpMqtt\Client\Facades\MQTT;

class ConnectClient extends Command
{
    /**
     * The name and signature of the console command.
     * Example usage: php artisan izisec:connect poc brinks dev_2
     */
    protected $signature = 'izisec:connect 
                            {location : The branch/location name} 
                            {sublocation : The building/sublocation name} 
                            {locationid : The room/device ID}
                            {--stop : Stop the connection and release the slot}';

    /**
     * The console command description.
     */
    protected $description = 'Manually activate a VPN slot for a client to enable SSH troubleshooting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Find the Device
        $location = strtolower($this->argument('location'));
        $sublocation = strtolower($this->argument('sublocation'));
        $locationid = strtolower($this->argument('locationid'));

        $this->info("Searching for device: $location / $sublocation / $locationid ...");

        // Matching logic based on your DeviceController 'store' logic
        // Assuming: branch = location, building = sublocation, room = locationid (or closest match)
        $device = Device::where('branch', $location)
            ->where('building', $sublocation)
            ->where('room', $locationid) // Adjust if 'room' isn't exactly 'locationid'
            ->first();

        if (!$device) {
            // Fallback: Try searching by publish_topic if exact columns don't match
            $searchString = "izisec/$location/$sublocation/$locationid";
            $device = Device::where('publish_topic', 'LIKE', "$searchString%")->first();
        }

        if (!$device) {
            $this->error("Device not found in database.");
            return 1;
        }

        $this->info("Found Device ID: " . $device->id . " | Topic: " . $device->publish_topic);

        // 2. Handle Stop Request
        if ($this->option('stop')) {
            $this->releaseSlot($device);
            return 0;
        }

        // 3. Assign Slot (Start Request)
        $this->assignSlot($device);
        return 0;
    }

    private function getSlotsFilePath()
    {
        return storage_path('app/vpn_slots.json');
    }

    private function assignSlot($device)
    {
        $path = $this->getSlotsFilePath();
        if (!file_exists($path)) {
            $this->error("Slot file missing at $path");
            return;
        }

        $assignedSlotIndex = null;
        $assignedIp = null;

        $fp = fopen($path, 'r+');
        if (flock($fp, LOCK_EX)) {
            $fileContent = fread($fp, filesize($path));
            $slots = json_decode($fileContent, true);

            // Check existing
            foreach ($slots as $key => $slot) {
                if ($slot['assigned_to'] == $device->id) {
                    $assignedSlotIndex = $key;
                    $assignedIp = $slot['ip'];
                    $slots[$key]['timestamp'] = time();
                    break;
                }
            }

            // Find free
            if ($assignedSlotIndex === null) {
                foreach ($slots as $key => $slot) {
                    $isStale = ($slot['timestamp'] > 0 && (time() - $slot['timestamp'] > 3600));
                    if (is_null($slot['assigned_to']) || $isStale) {
                        $slots[$key]['assigned_to'] = $device->id;
                        $slots[$key]['timestamp'] = time();
                        $assignedSlotIndex = $key;
                        $assignedIp = $slot['ip'];
                        break;
                    }
                }
            }

            // Write back
            if ($assignedSlotIndex !== null) {
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($slots, JSON_PRETTY_PRINT));
            }
            
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);

        if ($assignedSlotIndex === null) {
            $this->error("System Busy: No free VPN slots available.");
            return;
        }

        // 4. Send MQTT Command
        try {
            $mqtt = MQTT::connection();
            $payload = json_encode([
                "cmd" => "startstream",
                "slot" => $assignedSlotIndex
            ]);
            $mqtt->publish($device->publish_topic, $payload, 1);
            $mqtt->loop(false, true);

            $this->info("------------------------------------------------");
            $this->info("SUCCESS! Slot #$assignedSlotIndex Assigned.");
            $this->info("VPN IP: $assignedIp");
            $this->info("------------------------------------------------");
            $this->comment("You can now SSH into the device:");
            $this->line("ssh root@$assignedIp"); // Assuming user is root
            $this->info("------------------------------------------------");

        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
        }
    }

    private function releaseSlot($device)
    {
        $path = $this->getSlotsFilePath();
        if (file_exists($path)) {
            $fp = fopen($path, 'r+');
            if (flock($fp, LOCK_EX)) {
                $fileContent = fread($fp, filesize($path));
                $slots = json_decode($fileContent, true);
                
                $updated = false;
                foreach ($slots as $key => $slot) {
                    if ($slot['assigned_to'] == $device->id) {
                        $slots[$key]['assigned_to'] = null;
                        $slots[$key]['timestamp'] = 0;
                        $updated = true;
                        $this->info("Released Slot #$key");
                    }
                }

                if ($updated) {
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, json_encode($slots, JSON_PRETTY_PRINT));
                }
                
                fflush($fp);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

        try {
            $mqtt = MQTT::connection();
            $mqtt->publish($device->publish_topic, "stopstream", 1);
            $mqtt->loop(false, true);
            $this->info("Stop command sent via MQTT.");
        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
        }
    }
}