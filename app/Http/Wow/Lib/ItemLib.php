<?php

namespace App\Http\Wow\Lib;

class ItemLib
{
    static $itemNameList = null;

    public static function checkItem($itemName, $connection)
    {
        $itemName = trim($itemName);
        if (self::$itemNameList === null) {
            self::$itemNameList = $connection->table('dat_item')->pluck('itemname');
        }
        if (self::$itemNameList->contains($itemName)) {
            return true;
        }

        self::$itemNameList->add($itemName);
        return false;
    }
}