<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    protected $table = 'dat_item';
    protected $guarded = ['id'];
    public $timestamps = false;
}