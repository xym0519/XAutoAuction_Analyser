<?php

namespace App\Http\Example\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by Broche.
 * Date: 2020-02-04
 */
class ExampleModel extends Model
{
    public $timestamps = false;
    protected $table = 'example';
    protected $guarded = ['id'];
}