<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;

class ResponseController extends Controller
{
    public function response(Request $request)
    {
        $visitor_name = $request->query('visitor_name', 'Guest');
        //$client_ip = '102.89.22.254';
        $client_ip = $request->ip();
        
        $location = $this->getLocationFromIp($client_ip);
        $city = $location['name'] ?? 'Unknown';
        $latitude = $location['lat'] ?? null;
        $longitude = $location['lon'] ?? null;
        
        $temperature = ($latitude && $longitude) ? $this->getTemperature($latitude, $longitude) : 'N/A';

        return response()->json([
            'client_ip' => $client_ip,
            'location' => $city,
            'greeting' => "Hello, $visitor_name! The temperature is $temperature degrees Celsius in $city"
        ]);
    }

    private function getLocationFromIp($ip)
    {
        $client = new Client();
        $apiKey = env('WEATHERAPI_KEY');
        $response = $client->get("http://api.weatherapi.com/v1/search.json", [
            'query' => [
                'key' => $apiKey,
                'q' => $ip
            ]
        ]);

        $locations = json_decode($response->getBody(), true);
        
        return $locations[0] ?? [];
    }

    private function getTemperature($latitude, $longitude)
    {
        $client = new Client();
        $apiKey = env('OPENWEATHERMAP_KEY');
        $response = $client->get("https://api.openweathermap.org/data/2.5/weather", [
            'query' => [
                'lat' => $latitude,
                'lon' => $longitude,
                'units' => 'metric',
                'appid' => $apiKey
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['main']['temp'] ?? 'N/A';
    }
}