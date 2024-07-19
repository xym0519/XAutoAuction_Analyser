<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class PriceHistoryModel extends Model
{
    protected $table = 'pricehistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}