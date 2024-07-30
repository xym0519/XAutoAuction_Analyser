<?php

namespace Wow;

use App\Http\Wow\Lib\XAutoAuctionLib;
use Illuminate\Support\Facades\DB;
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

    public function testStatistics() {
        $connection = DB::connection('mysql1');
        $connection->beginTransaction();
        XAutoAuctionLib::analyse($connection);
        $connection->commit();
    }
}
