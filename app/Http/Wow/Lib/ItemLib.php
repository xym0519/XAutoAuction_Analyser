<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\ItemModel;

class ItemLib
{
    static $itemNameList = null;

    public static function checkItem($itemName, $dbIndex)
    {
        $itemName = trim($itemName);
        if (self::$itemNameList === null) {
            self::$itemNameList = ItemModel::on('mysql' . $dbIndex)->pluck('itemname');
        }
        if (self::$itemNameList->contains($itemName)) {
            return true;
        }

        ItemModel::on('mysql' . $dbIndex)->create(['itemname' => $itemName]);
        self::$itemNameList->add($itemName);
        return false;
    }
}