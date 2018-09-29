<?php

namespace App\Traits;

use App\Exceptions\FailedToSendGuzzleRequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

trait GuzzleTrait {
    
    /**
     * this generic method is used to send a 'GET' request to the provided $url param
     * then decodes the resonse into an array and return it
     * if it encountres any problem, it will thow an excption
     *
     * @param [String] $url
     * @return void
     */
    protected function getDecodedDataFromUrl($url) {

        try {

            $client = new Client();
            $result = $client->get($url);

            if($result->getStatusCode() == 200) {
                return json_decode($result->getBody(), true);
            } else {
                throw new FailedToSendGuzzleRequestException();
            }
    

        } catch (\Exception $e) {

            \Log::error(['error while fetching the feed' => $e]);
            throw new FailedToSendGuzzleRequestException();

        }
    }    
}