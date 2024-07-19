<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class TradeHistoryDetailModel extends Model
{
    protected $table = 'tradehistorydetail';
    protected $guarded = ['id'];
    public $timestamps = false;
}