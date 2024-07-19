<?php
//
//namespace App\Http\Wow\Lib;
//
//use App\Http\Wow\Model\TradeHistoryModel;
//use Illuminate\Support\Facades\DB;
//
//class TradeLib
//{
//    static function analyse($filePath, $dbIndex)
//    {
//        if (!file_exists($filePath)) {
//            echo sprintf("File: %s\nNot Found. Skipped.\n\n", $filePath);
//            return;
//        }
//
//        $result = CommonLib::luaFile2Json($filePath);
//
//        $tradecount = 0;
//        $inmoney = 0;
//        $outmoney = 0;
//        $initemcount = 0;
//        $outitemcount = 0;
//
//        $tradeSrcList = $result['DuowanAddon_TradeLog_TradesHistory'];
//        $tradeSrcMap = [];
//        foreach ($tradeSrcList as $item) {
//            if ($item['result'] != 'complete') {
//                continue;
//            }
//            $key = $item['id'] . '_' . $item['when'];
//            $tradeSrcMap[$key] = $item;
//        }
//        if (empty($tradeSrcMap)) {
//            return;
//        }
//        $tradeDBList = TradeHistoryModel::on('mysql' . $dbIndex)->whereIn('key', array_keys($tradeSrcMap))->pluck('key');
//        foreach ($tradeDBList as $key) {
//            unset($tradeSrcMap[$key]);
//        }
//        $tradeDetailList = [];
//        foreach ($tradeSrcMap as $key => $trade) {
//            $year = date('Y');
//            if (abs(time() - strtotime($year . '-' . $trade['when'])) < 30 * 24 * 3600) {
//                $tradeTime = strtotime($year . '-' . $trade['when']);
//            } else {
//                $tradeTime = strtotime(($year - 1) . '-' . $trade['when']);
//            }
//            $tradeItem = TradeHistoryModel::on('mysql' . $dbIndex)->create([
//                'key' => $key,
//                'player' => $trade['player'],
//                'target' => $trade['who'],
//                'playermoney' => $trade['playerMoney'],
//                'targetmoney' => $trade['targetMoney'],
//                'playeritemcount' => count($trade['playerItems']),
//                'targetitemcount' => count($trade['targetItems']),
//                'tradetime' => $tradeTime,
//                'createtime' => time()
//            ]);
//            $inmoney += $trade['targetMoney'];
//            $outmoney += $trade['playerMoney'];
//
//            foreach ($trade['playerItems'] as $detail) {
//                if (!is_array($detail)) {
//                    continue;
//                }
//                $itemName = trim($detail['name']);
//                ItemLib::checkItem($itemName, $dbIndex);
//
//                $tradeDetailList[] = [
//                    'tradehistoryid' => $tradeItem->id,
//                    'inout' => 'out',
//                    'itemname' => $itemName,
//                    'count' => $detail['numItems'],
//                    'createtime' => time()
//                ];
//            }
//            $outitemcount += count($trade['playerItems']);
//
//            foreach ($trade['targetItems'] as $detail) {
//                if (!is_array($detail)) {
//                    continue;
//                }
//                $itemName = trim($detail['name']);
//                ItemLib::checkItem($itemName, $dbIndex);
//
//                $tradeDetailList[] = [
//                    'tradehistoryid' => $tradeItem->id,
//                    'inout' => 'in',
//                    'itemname' => $itemName,
//                    'count' => $detail['numItems'],
//                    'createtime' => time()
//                ];
//            }
//            $initemcount += count($trade['targetItems']);
//
//            $tradecount++;
//        }
//
//        DB::connection('mysql' . $dbIndex)->table('tradehistorydetail')->insert($tradeDetailList);
//
////        echo sprintf("File: %s\nTradeCount: %s\tInMoney: %s\tOutMoney: %s\tInItems: %s\tOutItems: %s\n\n", $filePath, $tradecount, round($inmoney / 10000), round($outmoney / 10000), $initemcount, $outitemcount);
//    }
//}
