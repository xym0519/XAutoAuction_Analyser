<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class ImportSellHistoryModel extends Model
{
    protected $table = 'imp_sellhistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}