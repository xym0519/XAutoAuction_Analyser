<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\ItemModel;

class XXAuctionLib
{
    static function generate($filePaths, $dbIndex)
    {
        $items = ItemModel::on('mysql' . $dbIndex)->select('itemname', 'costprice', 'costprice10', 'sellprice', 'sellprice10', 'minsellprice', 'minsellprice10', 'maxsellprice', 'maxsellprice10', 'lowestprice', 'lowestprice10', 'dealcount', 'dealcount10', 'sellcount', 'sellcount10', 'dealproportion', 'dealproportion10')->get();
        $result = "XAuctionInfoList = {\n";
        $keys = ['costprice', 'costprice10', 'sellprice', 'sellprice10', 'minsellprice', 'minsellprice10', 'maxsellprice', 'maxsellprice10', 'lowestprice', 'lowestprice10', 'dealcount', 'dealcount10', 'sellcount', 'sellcount10', 'succrate', 'succrate10'];
        foreach ($items as $item) {
            $result .= sprintf("    [\"%s\"] = {\n", $item->itemname);
            $item->succrate = $item->dealproportion != 0 ? round(1 / $item->dealproportion, 1) : 999;
            $item->succrate10 = $item->dealproportion10 != 0 ? round(1 / $item->dealproportion10, 1) : 999;
            foreach ($item->toArray() as $k => $v) {
                if (!in_array($k, $keys)) {
                    continue;
                }
                $result .= sprintf("        [\"%s\"] = %s,\n", $k, $v);
            }
            $result .= "    },\n";
        }
        $result .= "}";
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            file_put_contents($filePath, $result);
            echo sprintf("File: %s Created.\nItems: %s.\n\n", $filePath, count($items));
        }
    }
}
