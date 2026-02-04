<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;

class LineService
{
    protected static $apiInstance = null;

    /**
     * Get or create the LINE Messaging API instance.
     */
    protected static function getApiInstance()
    {
        if (self::$apiInstance === null) {
            $accessToken = config('services.line.messaging_api.access_token');

            if (empty($accessToken)) {
                return null;
            }

            $client = new Client();
            $config = new Configuration();
            $config->setAccessToken($accessToken);

            self::$apiInstance = new MessagingApiApi(
                client: $client,
                config: $config
            );
        }

        return self::$apiInstance;
    }

    /**
     * Get the display name of a LINE user by their LINE User ID.
     * Caches the result for 1 hour to improve performance.
     *
     * @param string|null $lineUserId
     * @return string|null
     */
    public static function getProfileName(?string $lineUserId)
    {
        if (empty($lineUserId)) {
            return null;
        }

        // Cache key for the user profile
        $cacheKey = 'line_profile_name_' . $lineUserId;

        return Cache::remember($cacheKey, 60 * 60, function () use ($lineUserId) {
            try {
                $api = self::getApiInstance();
                if (!$api) {
                    return null;
                }

                $response = $api->getProfile($lineUserId);
                return $response->getDisplayName();
            } catch (\Exception $e) {
                // Log error but don't crash
                Log::error("Failed to fetch LINE profile for ID $lineUserId: " . $e->getMessage());
                return null;
            }
        });
    }
}
