<?php

namespace App\Http\Wow\Command;

use App\Http\Wow\Lib\XAutoAuctionLib;
use Illuminate\Console\Command;

class XAutoAuctionCommand extends Command
{
    protected $signature = 'xautoauction';

    public function handle()
    {
        echo "----------------------------------------\n";

        // XAutoAuction_Data
        $dataPaths = [
            'd:\Applications\Battle.net\World of Warcraft\_classic_\WTF\Account\150937928#1\SavedVariables\XAutoAuction_Data.lua'
        ];

        foreach ($dataPaths as $dataPath) {
            XAutoAuctionLib::process($dataPath, 1);
        }
    }
}