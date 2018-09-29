<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * The fillable attributes of the Message model
     */
    protected $fillable = [
        'body',
        'location_name',
        'lat',
        'lng',
        'sentiment'
    ];
}
