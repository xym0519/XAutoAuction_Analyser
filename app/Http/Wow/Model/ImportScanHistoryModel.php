<?php

namespace App\Http\Wow\Model;

use Illuminate\Database\Eloquent\Model;

class ImportScanHistoryModel extends Model
{
    protected $table = 'imp_scanhistory';
    protected $guarded = ['id'];
    public $timestamps = false;
}