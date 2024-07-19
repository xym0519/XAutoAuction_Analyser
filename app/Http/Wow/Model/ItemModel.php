<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    protected $table = 'item';
    protected $guarded = ['id'];
    public $timestamps = false;
}