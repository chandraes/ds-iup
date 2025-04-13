<?php

namespace App\Services;

class WaStatus
{
    private $apikey;

    private $apiDevKey;

    private $deviceId;

    public function __construct()
    {
        $this->apiDevKey = env('STARSENDER_API_KEY');
        $this->deviceId = env('STARSENDER_DEVICE_ID');
        $this->apikey = env('STARSENDER_KEY');
    }

    public function getStatusWa()
    {
        $apikey = $this->apiDevKey;

        $curl = curl_init();

        $idDevice = $this->deviceId;

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.starsender.online/api/devices/'.$idDevice,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type:application/json',
                'Authorization: '.$apikey,
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            // Handle curl error
            $error_message = curl_error($curl);
            curl_close($curl);

            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server Whatsapp, Silahkan hubungi administrator! Error: '.$error_message,
            ];
        }

        curl_close($curl);

        $result = json_decode($response, true);

        if (isset($result['success']) && $result['success'] == true) {
            return $result;
        } else {
            return [
                'success' => false,
                'message' => 'Server Whatsapp sedang mengalami gangguan, silahkan coba beberapa saat lagi!',
            ];
        }

    }

    public function getRelog()
    {
        $apikey = $this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://starsender.online/api/relogDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'apikey: '.$apikey,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);
        dd($result);

    }

    public function getGroup()
    {
        $apikey = $this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.starsender.online/api/whatsapp/groups',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Content-Type:application/json',
                'Authorization: '.$apikey,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);

        if ($result['success'] == true) {
            return $result;
        } else {
            return false;
        }
    }
}
