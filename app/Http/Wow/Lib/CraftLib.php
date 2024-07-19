<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\CraftHistoryModel;
use Illuminate\Support\Facades\DB;

class CraftLib
{
    static function analyse($filePath, $dbIndex)
    {
        if (!file_exists($filePath)) {
            echo sprintf("File: %s\nNot Found. Skipped.\n\n", $filePath);
            return;
        }

        $result = CommonLib::luaFile2Json($filePath);

        $count = 0;

        $craftList = $result['XXCraftHistory'];
        $craftItemList = [];
        foreach ($craftList as $key => $value) {
            $kl = explode(':', $key);
            $crafttime = $kl[1];
            $pattern = '/^.*\[([^]]+)]\|h\|rx?(\d+)?/';
            if (preg_match($pattern, $value, $matches)) {
                $itemName = trim($matches[1]);
                ItemLib::checkItem($itemName, $dbIndex);
                $count = empty($matches[2]) ? 1 : $matches[2];

                $craftItemList[$key] = [
                    'key' => $key,
                    'itemname' => $itemName,
                    'crafttime' => $crafttime,
                    'count' => $count,
                    'createtime' => time()
                ];
                $count++;
            }
            unset($matches);
        }

        if (!empty($craftItemList)) {
            $existedItemKeys = CraftHistoryModel::on('mysql' . $dbIndex)->whereIn('key', array_keys($craftItemList))->pluck('key');
            foreach ($existedItemKeys as $key) {
                if (array_key_exists($key, $craftItemList)) {
                    unset($craftItemList[$key]);
                    $count--;
                }
            }
            if (!empty($craftItemList)) {
                DB::connection('mysql' . $dbIndex)->table('crafthistory')->insert(array_values($craftItemList));
            }
        }

//        rename($filePath, $filePath . '_' . date('YmdHis'));

//        echo sprintf("File: %s\nCount: %s\n\n", $filePath, $count);
    }
}
