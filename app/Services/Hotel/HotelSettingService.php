<?php

namespace App\Services\Hotel;

use App\Events\Hotels\HotelSettingUpdated;
use App\Models\Hotel;
use App\Models\HotelSetting;
use Illuminate\Support\Facades\Cache;

class HotelSettingService
{
    /**
     * Retrieve hotel settings with caching.
     */
    public function getSettings(Hotel $hotel): HotelSetting
    {
        $cacheKey = "hotel:{$hotel->id}:settings";

        // Read the cache first.
        $cachedSettings = Cache::get($cacheKey);

        if ($cachedSettings instanceof HotelSetting) {
            return $cachedSettings;
        }

        // Retrieve the existing record or create it if it does not exist.
        $settings = HotelSetting::firstOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'currency' => 'JPY',
    		'timezone' => 'Asia/Tokyo',
    		'language' => 'en',
            ]
        );

        Cache::put($cacheKey, $settings, 86400);

        return $settings;
    }

    /**
     * Update an existing hotel settings record.
     */
    public function update(HotelSetting $setting, array $data): HotelSetting
    {
        $setting->update($data);

        $setting->refresh();

        Cache::forget("hotel:{$setting->hotel_id}:settings");

        event(new HotelSettingUpdated($setting));

        return $setting;
    }
}
