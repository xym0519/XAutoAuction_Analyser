<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class TradeHistoryModel extends Model
{
    protected $table = 'tradehistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}