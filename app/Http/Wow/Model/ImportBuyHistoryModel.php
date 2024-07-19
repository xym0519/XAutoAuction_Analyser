<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class ImportBuyHistoryModel extends Model
{
    protected $table = 'imp_buyhistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}