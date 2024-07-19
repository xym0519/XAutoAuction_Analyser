<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\ImportBuyHistoryModel;
use App\Http\Wow\Model\ImportSellHistoryModel;
use Illuminate\Support\Facades\DB;

class AuctionatorLib
{
    static function analyse($filePath, $dbIndex)
    {
        $result = CommonLib::luaFile2Json($filePath);

        $scanadd = 0;
        $selladd = 0;
        $buyadd = 0;

        $scanSrcList = $result['XXScanHistory'];
        $scanItemList = [];
        foreach ($scanSrcList as $k => $v) {
            $kList = explode(':', $k);
            $itemName = trim($kList[0]);
            $scanTime = $kList[1];

            ItemLib::checkItem($itemName, $dbIndex);

            $scanItemList[] = [
                'itemname' => $itemName,
                'scantime' => $scanTime,
                'minprice' => $v['minPrice'],
                'maxprice' => $v['maxPrice'],
                'sumprice' => $v['sumPrice'],
                'count' => $v['count'],
                'createtime' => time()
            ];
            $scanadd++;
        }
        if (!empty($scanItemList)) {
            DB::connection('mysql' . $dbIndex)->table('scanhistory')->insert($scanItemList);
        }

        $buySrcList = $result['XXBuyHistory'];
        $buyItemList = [];
        foreach ($buySrcList as $k => $v) {
            $kList = explode(':', $k);
            $itemName = trim($kList[0]);
            $buyTime = $kList[1];

            ItemLib::checkItem($itemName, $dbIndex);

            $buyItemList[$k] = [
                'key' => $k,
                'itemname' => $itemName,
                'buytime' => $buyTime,
                'price' => $v['price'],
                'count' => $v['count'],
                'createtime' => time()
            ];
        }
        if (!empty($buyItemList)) {
            $existedBuyItemKeys = ImportBuyHistoryModel::on('mysql' . $dbIndex)->whereIn('key', array_keys($buyItemList))->pluck('key');
            foreach ($existedBuyItemKeys as $item) {
                unset($buyItemList[$item]);
            }
            if (!empty($buyItemList)) {
                $buyadd = count($buyItemList);
                DB::connection('mysql' . $dbIndex)->table('buyhistory')->insert(array_values($buyItemList));
            }
        }

        $sellSrcList = $result['XXSellHistory'];
        $sellItemList = [];
        foreach ($sellSrcList as $k => $v) {
            $kList = explode(':', $k);
            $itemName = trim($kList[0]);
            $sellTime = $kList[1];

            ItemLib::checkItem($itemName, $dbIndex);

            $sellItemList[$k] = [
                'key' => $k,
                'itemname' => $itemName,
                'selltime' => $sellTime,
                'price' => $v['price'],
                'count' => $v['count'],
                'stackprice' => $v['stackPrice'],
                'stacksize' => $v['stackSize'],
                'stackcount' => $v['stackCount'],
                'createtime' => time()
            ];
        }
        if (!empty($sellItemList)) {
            $existedSellItemKeys = ImportSellHistoryModel::on('mysql' . $dbIndex)->whereIn('key', array_keys($sellItemList))->pluck('key');
            foreach ($existedSellItemKeys as $item) {
                unset($sellItemList[$item]);
            }
            if (!empty($sellItemList)) {
                $selladd = count($sellItemList);
                DB::connection('mysql' . $dbIndex)->table('sellhistory')->insert(array_values($sellItemList));
            }
        }

        rename($filePath, $filePath . '_' . date('YmdHis'));
        copy($filePath . '_xx', $filePath);

//        echo sprintf("File: %s\nScanAdded: %s\tBuyAdded: %s\tSellAdded: %s\n\n", $filePath, $scanadd, $buyadd, $selladd);
    }
}
