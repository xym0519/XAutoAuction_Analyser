<?php

namespace App\Http\Wow\Command;

use App\Http\Wow\Lib\AuctionatorLib;
use App\Http\Wow\Lib\CraftLib;
use App\Http\Wow\Lib\PostalLib;
use App\Http\Wow\Lib\StatisticsLib;
use App\Http\Wow\Lib\TradeLib;
use App\Http\Wow\Lib\XXAuctionLib;
use Illuminate\Console\Command;

class WowCommand2 extends Command
{
    protected $signature = 'wowauction2';

    public function handle()
    {
        echo "----------------------------------------\n";
        // Auctionator
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13961229851\乐悠二区\Miles\SavedVariables\Auctionator.lua',
            'd:\Games\Wow\WTF\Account\13376266608\乐悠二区\Bro\SavedVariables\Auctionator.lua',
        ];

        foreach ($filePaths as $filePath) {
            AuctionatorLib::analyse($filePath, 2);
        }

        // Postal
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13961229851\乐悠二区\Miles\SavedVariables\Postal.lua',
            'd:\Games\Wow\WTF\Account\13376266608\乐悠二区\Bro\SavedVariables\Postal.lua',
        ];

        foreach ($filePaths as $filePath) {
            PostalLib::analyse($filePath, 2);
        }

        // Trade
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13961229851\乐悠二区\Miles\SavedVariables\TheBurningTrade.lua',
            'd:\Games\Wow\WTF\Account\13376266608\乐悠二区\Bro\SavedVariables\TheBurningTrade.lua',
        ];

        foreach ($filePaths as $filePath) {
            TradeLib::analyse($filePath, 2);
        }

        // Craft
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13961229851\乐悠二区\Miles\SavedVariables\XXCraftRecord.lua',
            'd:\Games\Wow\WTF\Account\13376266608\乐悠二区\Bro\SavedVariables\XXCraftRecord.lua',
        ];
        foreach ($filePaths as $filePath) {
            CraftLib::analyse($filePath, 2);
        }

        // Analyse
        StatisticsLib::analyse(2);

        // XXAuction
        $filePaths = [
            'd:\Games\Wow\WTF\Account\13961229851\SavedVariables\XAutoAuction.lua',
            'd:\Games\Wow\WTF\Account\13376266608\SavedVariables\XAutoAuction.lua',
        ];
        XXAuctionLib::generate($filePaths, 2);
    }
}