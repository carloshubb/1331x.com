<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class BingIndexingService
{
    /**
     * Submit URLs to Bing URL Submission API
     *
     * @param array $urls Array of URLs to submit
     * @return array Response from Bing API
     */
    public function submitToBing(array $urls): array
    {
        $apiKey = Config::get('services.bing.api_key');
        $siteUrl = Config::get('services.bing.site_url', env('APP_URL'));

        if (empty($apiKey)) {
            Log::warning('Bing API key is not configured');
            return [
                'success' => false,
                'message' => 'Bing API key is not configured'
            ];
        }

        if (empty($urls)) {
            return [
                'success' => false,
                'message' => 'No URLs provided'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post(
                "https://ssl.bing.com/webmasters/api.svc/json/SubmitUrlbatch?apikey={$apiKey}",
                [
                    'siteUrl' => $siteUrl,
                    'urlList' => $urls
                ]
            );

            if ($response->successful()) {
                Log::info('Bing URL submission successful', [
                    'urls_count' => count($urls),
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message' => 'URLs submitted successfully',
                    'data' => $response->json()
                ];
            } else {
                Log::error('Bing URL submission failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Bing API request failed',
                    'status' => $response->status(),
                    'error' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Bing URL submission exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit a single URL to Bing
     *
     * @param string $url URL to submit
     * @return array Response from Bing API
     */
    public function submitUrl(string $url): array
    {
        return $this->submitToBing([$url]);
    }

    /**
     * Build full URL from relative path
     *
     * @param string $path Relative path (e.g., 'torrent/123/slug')
     * @return string Full URL
     */
    public function buildFullUrl(string $path): string
    {
        $baseUrl = rtrim(Config::get('services.bing.site_url', env('APP_URL')), '/');
        $path = ltrim($path, '/');
        return "{$baseUrl}/{$path}";
    }
}
