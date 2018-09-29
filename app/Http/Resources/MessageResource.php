<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'body' => $this->body,
            'location_name' => $this->location_name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'message_hour' => $this->message_hour,
            'message_date' => $this->message_date,
            'sentiment' => $this->sentiment,
        ];
    }
}
