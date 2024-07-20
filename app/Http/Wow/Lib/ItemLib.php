<?php

namespace App\Http\Wow\Lib;

class ItemLib
{
    static $itemNameList = null;

    public static function checkItem($item, $connection)
    {
        if (self::$itemNameList === null) {
            self::$itemNameList = $connection->table('dat_item')->pluck('itemname');
        }

        if (self::$itemNameList->contains($item->itemname)) {
            return true;
        }

        self::$itemNameList->push($item->itemname);
        return false;
    }
}