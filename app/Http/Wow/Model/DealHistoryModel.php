<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class DealHistoryModel extends Model
{
    protected $table = 'dealhistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}