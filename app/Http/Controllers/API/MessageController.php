<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\GuzzleTrait;
use App\Traits\ErrorResponseTrait;
use App\Exceptions\FailedToSendGuzzleRequestException;
use App\Message;
use App\Http\Resources\MessageResource;

class MessageController extends Controller
{

    use GuzzleTrait, ErrorResponseTrait;

    /**
     * What are trying to achieve by using this method?
     * we have an API, that contains an array of feeds
     * 1- we need to fetch these feeds
     * 2- we need to process these feeds to get meaningful data
     * 3- we need to prepare this meaningful data to send it to the client through our API
     * 
     * How are we gonna do it?
     * 1- use 'GuzzleTrait' trait to send a 'GET' request to the feed api
     * 2- check if the response of the feed api contains any feeds at all
     * 3- loop over those feeds, each field has a message that contains the location
     * 4- use this string to send a request to google maps API to fetch the matched results
     * 5- the matches results contains (lat, lng, name) , we'll use them to create a message object along with the message itself
     * 6- append each message object into the final collection that will be sent to the client
     * 7- send the collection to a resource file in order to customize the keys
     * 8- send the customized collection to the client
     */
    public function fetchFeed(Request $request)
    {
        try {

            // utlizing guzzle trait to call the feed api
            $decoded_response = $this->getDecodedDataFromUrl(env('FEED_URL'));

            // extracting the entries
            $entries = $this->extractEntriesFromResponse($decoded_response);

            if(count($entries) > 0) {

                // creating an empty collection that will contain all the messages
                $feeds_collection = collect();

                // looping through the entries
                foreach($entries as $e) {

                    // the raw message of each entry
                    $raw_message = $this->extractEntryFromResponse($e);

                    try {
                        
                        $google_maps_api_url = env('GOOGLE_MAPS_API') . '?address=' . urlencode($raw_message) . '&key=' . env('GOOGLE_MAPS_KEY');

                        // utlizing guzzle trait to call google maps api
                        $decoded_matched_locations = $this->getDecodedDataFromUrl($google_maps_api_url);

                        // checking if the location if found
                        if(!empty($decoded_matched_locations['results'][0])) {

                            // extracting the location, then creating a message from it
                            $matched_location = $decoded_matched_locations['results'][0];
                            $message = $this->createMessageInstance($raw_message, $matched_location);
                            
                            // pushong into the collection
                            $feeds_collection->push($message);

                        }


                    } catch (FailedToSendGuzzleRequestException $e) {
                        \Log::error(['error' => $e->getMessage()]);
                        return $this->sendError('Error while sending a request to google maps api');
                    } catch (\Exception $e) {
                        \Log::error(['error' => $e->getMessage()]);
                        return $this->sendError('General error while sending a request to google maps api');
                    }

                }

                return (MessageResource::collection($feeds_collection))
                    ->additional(['meta' => [
                        'success' => true,
                    ]])
                    ->response()
                    ->setStatusCode(200);

            } else {

                return $this->sendError('Error while fetching data from the feed source, no entries');

            }

        } catch (FailedToSendGuzzleRequestException $e) {
            \Log::error(['error' => $e->getMessage()]);
            return $this->sendError('Error while fetching data from the feed source');

        } catch (\Exception $e) {
            \Log::error(['error' => $e->getMessage()]);
            return $this->sendError('General error while fetching data from the feed source');
        }
    }

    /**
     * this method takes the decoded reponse from the feed request, then checks if the 'entry' key exists
     * which contains array of objects (messages)
     * if so, it will return it
     * else, returns null
     *
     * @param [array<String>] $decoded_response
     * @return void
     */
    private function extractEntriesFromResponse($decoded_response)
    {
        if(!empty($decoded_response['feed']['entry']))
            return $decoded_response['feed']['entry'];
        else
            return null;
    }

    /**
     * this method takes a single entry, checks if the key '$t' exists, which contains a single message
     *
     * @param [String] $entry
     * @return void
     */
    private function extractEntryFromResponse($entry)
    {
        if(!empty($entry['content']['$t']))
            return $entry['content']['$t'];
        else
            return null;
    }

    /**
     * this method returns the 'sentiment' field of the message if it exists
     * else, it will return 'N/A'
     */
    private function getFormattedSentiment($raw_message)
    {
        if(!stripos($raw_message, 'sentiment: '))
            return 'N/A';
        else
            return explode('sentiment: ', strtolower($raw_message))[1];
        
    }

    /**
     * this method returns the formatted message after exploding the whole string
     */
    private function getFormattedMessage($raw_message)
    {
        if(!stripos($raw_message, 'message: '))
            return 'N/A';
        else {
            $message_part = explode('message: ', $raw_message)[1];
            return explode(', ', $message_part)[0];
        }
    }

    /**
     * this method is used to create a message object, later this object will be push into a collection
     */
    private function createMessageInstance($raw_message, $matched_location)
    {
        $location_name = $matched_location['address_components'][0]['short_name'];
        $lat = $matched_location['geometry']['location']['lat'];
        $lng = $matched_location['geometry']['location']['lng'];
        $sentiment = $this->getFormattedSentiment($raw_message);

        return new Message ([
            'body' => $this->getFormattedMessage($raw_message),
            'location_name' => $location_name,
            'lat' => $lat,
            'lng' => $lng,
            'sentiment' => $sentiment
        ]);
    }
}
