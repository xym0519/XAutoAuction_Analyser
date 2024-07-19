<?php

namespace Wow;

use App\Http\Wow\Lib\XAutoAuctionLib;
use TestCase;

class WowTest extends TestCase
{
    public function testXAutoAuction()
    {
        $filePaths = [
            'd:\Applications\Battle.net\World of Warcraft\_classic_\WTF\Account\150937928#1\SavedVariables\XAutoAuction_Data.lua',
        ];

        foreach ($filePaths as $filePath) {
            XAutoAuctionLib::process($filePath, 1);
        }
    }
}
