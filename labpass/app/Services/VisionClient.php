<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VisionClient
{
    public function detect(array $imageData): array
    {
        $driver = config('services.vision.driver', 'mock');
        $url = config('services.vision.url', 'http://127.0.0.1:8001');

        if ($driver === 'http') {
            return $this->detectViaHttp($imageData, $url);
        }

        // Default to mock detection
        return $this->detectMock();
    }

    private function detectViaHttp(array $imageData, string $url): array
    {
        try {
            $response = Http::post("{$url}/detect", [
                'image' => $imageData,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            // Return mock data on failure
            return $this->detectMock();
        }
    }

    private function detectMock(): array
    {
        // Mock detection - sometimes complete, sometimes incomplete
        $detections = [];
        $complete = rand(0, 10) > 3; // 70% chance of complete
        
        if ($complete) {
            // Complete detection
            $detections = [
                ['label' => 'jas_lab', 'conf' => 0.95, 'bbox' => [100, 100, 200, 200]],
                ['label' => 'kacamata', 'conf' => 0.92, 'bbox' => [150, 150, 250, 250]],
                ['label' => 'sarung_tangan', 'conf' => 0.90, 'bbox' => [120, 120, 220, 220]],
                ['label' => 'masker', 'conf' => 0.88, 'bbox' => [130, 130, 230, 230]],
            ];
        } else {
            // Incomplete detection - missing some items
            $missingItems = ['kacamata', 'sarung_tangan'];
            $detections = [
                ['label' => 'jas_lab', 'conf' => 0.95, 'bbox' => [100, 100, 200, 200]],
                ['label' => 'masker', 'conf' => 0.88, 'bbox' => [130, 130, 230, 230]],
            ];
        }

        return [
            'detections' => $detections,
            'annotated_image' => null,
        ];
    }
}