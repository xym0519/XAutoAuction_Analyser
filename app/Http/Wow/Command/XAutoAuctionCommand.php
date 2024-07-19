<?php

namespace App\Http\Wow\Command;

use App\Http\Wow\Lib\CraftLib;
use App\Http\Wow\Lib\PostalLib;
use App\Http\Wow\Lib\StatisticsLib;
use App\Http\Wow\Lib\TradeLib;
use App\Http\Wow\Lib\XAutoAuctionLib;
use App\Http\Wow\Lib\XXAuctionLib;
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
//
//        // Trade
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\TheBurningTrade.lua',
//            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\TheBurningTrade.lua'
//        ];
//
//        foreach ($filePaths as $filePath) {
//            TradeLib::analyse($filePath, 1);
//        }
//
//        // Craft
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\XXCraftRecord.lua',
//        ];
//        foreach ($filePaths as $filePath) {
//            CraftLib::analyse($filePath, 1);
//        }

        // Analyse
        StatisticsLib::analyse(1);

        // XXAuction
        $filePaths = [
            'd:\Games\Wow\WTF\Account\15061125778\SavedVariables\XAutoAuction.lua',
            'd:\Games\Wow\WTF\Account\13382850839\SavedVariables\XAutoAuction.lua'
        ];
        XXAuctionLib::generate($filePaths, 1);
    }
}