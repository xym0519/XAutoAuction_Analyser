<?php

namespace App\Http\Wow\Command;

use App\Http\Wow\Lib\AuctionatorLib;
use App\Http\Wow\Lib\CraftLib;
use App\Http\Wow\Lib\PostalLib;
use App\Http\Wow\Lib\StatisticsLib;
use App\Http\Wow\Lib\TradeLib;
use App\Http\Wow\Lib\XXAuctionLib;
use Illuminate\Console\Command;

class WowCommand extends Command
{
    protected $signature = 'wowauction';

    public function handle()
    {
        echo "----------------------------------------\n";
        // Auctionator
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Auctionator.lua',
            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\Auctionator.lua'
        ];

        foreach ($filePaths as $filePath) {
            AuctionatorLib::analyse($filePath, 1);
        }

        // Postal
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua',
            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\Postal.lua'
        ];

        foreach ($filePaths as $filePath) {
            PostalLib::analyse($filePath, 1);
        }

        // Trade
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\TheBurningTrade.lua',
            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\TheBurningTrade.lua'
        ];

        foreach ($filePaths as $filePath) {
            TradeLib::analyse($filePath, 1);
        }

        // Craft
        $filePaths = [
            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\XXCraftRecord.lua',
        ];
        foreach ($filePaths as $filePath) {
            CraftLib::analyse($filePath, 1);
        }

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