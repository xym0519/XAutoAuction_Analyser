<?php

namespace Wow;

use App\Http\Wow\Lib\AuctionatorLib;
use App\Http\Wow\Lib\CraftLib;
use App\Http\Wow\Lib\PostalLib;
use App\Http\Wow\Lib\TradeLib;
use App\Http\Wow\Lib\XAutoAuctionLib;
use App\Http\Wow\Lib\XXAuctionLib;
use TestCase;

class WowTest extends TestCase
{
    public function testXAutoAuction()
    {
        $filePaths = [
            'd:\Applications\Battle.net\World of Warcraft\_classic_\WTF\Account\150937928#1\SavedVariables\XAutoAuction_Data.lua',
//            'd:\Games\Wow\WTF\Account\13382850839\SavedVariables\Auctionator.lua'
        ];

        foreach ($filePaths as $filePath) {
            XAutoAuctionLib::process($filePath, 1);
        }
    }
//    public function testAuctionator()
//    {
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Auctionator.lua_20231201031950',
////            'd:\Games\Wow\WTF\Account\13382850839\SavedVariables\Auctionator.lua'
//        ];
//
//        foreach ($filePaths as $filePath) {
//            AuctionatorLib::analyse($filePath);
//        }
//    }

//    public function testPostal()
//    {
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231220130408',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231220102849',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231220031630',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231219022927',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231218234653',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231215021112',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231214221457',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231214204911',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231214030454',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231214015454',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231214000944',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231213030009',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231213015715',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231212023045',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231212010217',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231211231206',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231211133550',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231211023038',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231211004445',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231210234422',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231210141444',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231210032323',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231210031235',
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\Postal.lua_20231210022644',
//        ];
//
//        foreach ($filePaths as $filePath) {
//            PostalLib::analyse($filePath, 1);
//        }
//    }

//    public function testTrade()
//    {
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\13382850839\乐悠一区\暗影肌\SavedVariables\TheBurningTrade.lua',
//        ];
//
//        foreach ($filePaths as $filePath) {
//            TradeLib::analyse($filePath);
//        }
//    }

//    public function testXXAuction()
//    {
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\15061125778\SavedVariables\XXAuction.lua',
//            'd:\Games\Wow\WTF\Account\13382850839\SavedVariables\XXAuction.lua'
//        ];
//        XXAuctionLib::generate($filePaths);
//    }

//    public function testCraft()
//    {
//        $filePaths = [
//            'd:\Games\Wow\WTF\Account\15061125778\乐悠一区\默法\SavedVariables\XXCraftRecord.lua',
//        ];
//        foreach ($filePaths as $filePath) {
//            CraftLib::analyse($filePath);
//        }
//    }
}
