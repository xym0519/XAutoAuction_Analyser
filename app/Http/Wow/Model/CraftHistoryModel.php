<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class CraftHistoryModel extends Model
{
    protected $table = 'crafthistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}