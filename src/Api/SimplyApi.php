<?php

namespace Jkdow\SimplyBook\Api;

use Exception;
use Jkdow\SimplyBook\Support\Logger;
use Jkdow\SimplyBook\Api\ApiToken;

class SimplyApi
{
    protected static $client;

    public static function init($pluginRoot)
    {
        $token = new ApiToken($pluginRoot);
        Logger::info('Using token', [$token]);
        Logger::info('Using company', [config('api.company')]);
        self::$client = new JsonRpcClient('https://user-api.simplybook.me' . '/admin/', array(
            'headers' => array(
                'X-Company-Login: ' . config('api.company'),
                'X-User-Token: ' . $token->token(),
            )
        ));
    }

    public static function getEventList()
    {
        try {
            $response = self::$client->getEventList();
        } catch (Exception $e) {
            Logger::error('Error making API call', [$e->getMessage(), self::$client]);
            return;
        }
        return $response->map(function ($event) {
            return collect($event)->only('id', 'name');
        });
    }

    public static function getAdditionalFields($bookingId)
    {
        $response = self::$client->getBookingDetails($bookingId);
        return collect($response['additional_fields']);
    }

    public static function partiesThisMonth()
    {
        $filters = [
            'created_date_from' => date('Y-m-01'),
            'created_date_to' => date('Y-m-t'),
            'event_id' => config('bookings.partyid'),
            'is_confirmed' => 1,
        ];
        $response = self::$client->getBookings($filters);
        return $response->map(function ($booking) {
            //Logger::dump("Booking", $booking);
            return collect($booking)->only('id', 'start_date', 'record_date', 'client', 'unit');
        });
    }

    public static function previousParties($start, $end)
    {
        $filters = [
            'date_from' => $start,
            'date_to' => $end,
            'event_id' => config('bookings.partyid'),
            'is_confirmed' => 1,
        ];
        $response = self::$client->getBookings($filters);
        $totalBookings = $response->count();
        Logger::debug('Got response', [$totalBookings]);
        return $response->map(function ($booking, $index) use ($totalBookings) {
            Logger::debug("Processing booking " . ($index + 1) . " of " . $totalBookings);
            $fields = SimplyApi::getAdditionalFields($booking['id']);
            $name_data = $fields->first(fn($field) => $field['field_title'] === 'Child\'s Name') ?? [];
            $child_name = $name_data['value'] ?? null;
            $data = collect($booking)->only('id', 'start_date', 'record_date', 'client', 'unit', 'client_email');
            $data['child_name'] = $child_name;
            return $data;
        });
    }

}
