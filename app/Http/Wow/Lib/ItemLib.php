<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\ItemModel;

class ItemLib
{
    static $itemNameList = null;

    public static function checkItem($itemName, $connection)
    {
        $itemName = trim($itemName);
        if (self::$itemNameList === null) {
            self::$itemNameList = ItemModel::on($connection)->pluck('itemname');
        }
        if (self::$itemNameList->contains($itemName)) {
            return true;
        }

        self::$itemNameList->add($itemName);
        return false;
    }
}