<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\DealHistoryModel;
use Illuminate\Support\Facades\DB;

class PostalLib
{
    static function analyse($filePath, $dbIndex)
    {
        if (!file_exists($filePath)) {
            echo sprintf("File: %s\nNot Found. Skipped.\n\n", $filePath);
            return;
        }

        $result = CommonLib::luaFile2Json($filePath);

        $successcount = 0;
        $failcount = 0;

        $mailList = $result['XXSH'];
        $mailItemList = [];
        foreach ($mailList as $key => $value) {
            $kl = explode(':', $key);
            $mailtime = $kl[1];
            $pattern_s = '/^.*拍卖成功：(\S+) \[((\d+)\|TInterface\\\\\\\\MoneyFrame\\\\\\\\UI-GoldIcon:0:0:2:0\|t )?((\d+)\|TInterface\\\\\\\\MoneyFrame\\\\\\\\UI-SilverIcon:0:0:2:0\|t )?(\d+)\|TInterface\\\\\\\\MoneyFrame\\\\\\\\UI-CopperIcon:0:0:2:0\|t\]/';
            if (preg_match($pattern_s, $value, $matches)) {
                $itemName = trim($matches[1]);
                ItemLib::checkItem($itemName, $dbIndex);

                $g = intval($matches[3]);
                $y = intval($matches[5]);
                $t = intval($matches[6]);
                $mailItemList[$key] = [
                    'key' => $key,
                    'issuccess' => 1,
                    'itemname' => $itemName,
                    'totalprice' => $g * 10000 + $y * 100 + $t,
                    'dealtime' => $mailtime,
                    'createtime' => time()
                ];
                $successcount++;
            }
            unset($matches);
            $pattern_s = '/^.*(拍卖已到期|拍卖取消)：(\S+)/';
            if (preg_match($pattern_s, $value, $matches)) {
                $itemName = trim($matches[2]);
                ItemLib::checkItem($itemName, $dbIndex);

                $mailItemList[$key] = [
                    'key' => $key,
                    'issuccess' => 0,
                    'itemname' => $itemName,
                    'totalprice' => 0,
                    'dealtime' => $mailtime,
                    'createtime' => time()
                ];
                $failcount++;
            }
            unset($matches);
        }

        if (!empty($mailItemList)) {
            $existedItemKeys = DealHistoryModel::on('mysql' . $dbIndex)->whereIn('key', array_keys($mailItemList))->select('key', 'issuccess')->get();
            foreach ($existedItemKeys as $item) {
                if (array_key_exists($item->key, $mailItemList)) {
                    unset($mailItemList[$item->key]);
                    continue;
                }
                if ($item->issuccess) {
                    $successcount--;
                } else {
                    $failcount--;
                }
            }
            if (!empty($mailItemList)) {
                DB::connection('mysql' . $dbIndex)->table('dealhistory')->insert(array_values($mailItemList));
            }
        }

        rename($filePath, $filePath . '_' . date('YmdHis'));

        echo sprintf("File: %s\nSuccess: %s\tFail: %s\n\n", $filePath, $successcount, $failcount);
    }
}
