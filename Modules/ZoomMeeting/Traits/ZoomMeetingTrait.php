<?php

namespace Modules\ZoomMeeting\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait ZoomMeetingTrait
{
    public $client;
    public $headers;
    public $meeting_url = "https://api.zoom.us/v2/";

    public function __construct()
    {
        $this->client = new Client();
    }

    private function retrieveZoomUrl()
    {
        return $this->meeting_url;
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomMeetingTrait->toZoomTimeFormat : ' . $e->getMessage());
            return '';
        }
    }

    public function createmitting($data)
    {
        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->getHeader(),
            'body'    => json_encode([
                'topic'      => $data['topic'],
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'password'   => $data['password'],
                'agenda'     => $data['agenda'] ?? null,
                'timezone'   => $this->getTimezone(),
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1"),
                    'participant_video' => ($data['participant_video'] == "1"),
                    'waiting_room'      => true,
                ],
            ]),
        ];

        $response = $this->client->post($url . $path, $body);

        return [
            'success' => $response->getStatusCode() === 201,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function meetingUpdate($id, $data)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->getHeader(),
            'body'    => json_encode([
                'topic'      => $data['topic'],
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => $data['agenda'] ?? null,
                'timezone'   => $this->getTimezone(),
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1"),
                    'participant_video' => ($data['participant_video'] == "1"),
                    'waiting_room'      => true,
                ],
            ]),
        ];

        $response = $this->client->patch($url . $path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function get($id)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $response = $this->client->get($url . $path, [
            'headers' => $this->getHeader(),
        ]);

        return [
            'success' => $response->getStatusCode() === 200,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function delete($id)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $response = $this->client->delete($url . $path, [
            'headers' => $this->getHeader(),
        ]);

        return [
            'success' => $response->getStatusCode() === 204,
        ];
    }

    public function getHeader()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    public function getToken()
    {
        $accountId     = $this->globalSetting('zoom_account_id');
        $clientId      = $this->globalSetting('zoom_client_id');
        $clientSecret  = $this->globalSetting('zoom_client_secret');

        if (!empty($accountId) && !empty($clientId) && !empty($clientSecret)) {
            $basicAuthHeader = base64_encode($clientId . ':' . $clientSecret);

            $response = $this->client->request('POST', 'https://zoom.us/oauth/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . $basicAuthHeader,
                ],
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $accountId,
                ],
            ]);

            $token = json_decode($response->getBody(), true);

            if (isset($token['access_token'])) {
                return $token['access_token'];
            }
        }

        return false;
    }

    public function getTimezone()
    {
        return $this->globalSetting('timezone') ?? config('app.timezone');
    }

    public function globalSetting($key)
    {
        return \App\Models\Setting::where('key', $key)->value('value');
    }
}
