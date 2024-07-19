<?php

namespace App\Http\Wow\Lib;

use App\Http\Wow\Model\ItemModel;
use Illuminate\Support\Facades\DB;

class StatisticsLib
{
    static function analyse($dbIndex)
    {
        // 从trade同步到buy
        DB::connection('mysql' . $dbIndex)->insert("insert into buyhistory(`key`, itemname, price, `count`, buytime, source, createtime)
                            select concat(a.id, '_', b.key),
                                   a.itemname,
                                   ifnull(c.tradeprice, 0),
                                   a.count,
                                   b.tradetime,
                                   'trade',
                                   unix_timestamp()
                            from tradehistorydetail a
                                     inner join tradehistory b on a.tradehistoryid = b.id
                                     inner join item c on a.itemname = c.itemname
                            where b.issynced = 0
                              and a.`inout` = 'in'
                              and b.target not in ('暗影肌', '默法', 'Miles', '得了吧', '悠优', 'Bro', 'Brobro')");
        DB::connection('mysql' . $dbIndex)->update('update tradehistory set issynced = 1');

        // 更新日期
        DB::connection('mysql' . $dbIndex)->update("update dealhistory set dealdate = from_unixtime(dealtime, '%Y-%m-%d') where dealdate = ''");
        DB::connection('mysql' . $dbIndex)->update("update buyhistory set buydate = from_unixtime(buytime, '%Y-%m-%d') where buydate = ''");

        // 更新item.buyprice/10
        DB::connection('mysql' . $dbIndex)->update('update item a left join (select a.itemname, round(avg(b.price)) price
                                                     from item a
                                                              inner join buyhistory b on a.itemname = b.itemname
                                                     group by a.itemname) z on a.itemname = z.itemname
                           set a.buyprice=ifnull(z.price, a.tradeprice)');
        DB::connection('mysql' . $dbIndex)->update('update item a set a.buyprice10 = ifnull((select round(avg(b.price)) from (select price from buyhistory c where c.itemname = a.itemname and buytime > unix_timestamp()-10*24*3600) b), a.tradeprice)');

        // 更新item.makeprice/10
        DB::connection('mysql' . $dbIndex)->update('update item a inner join (select z.itemname, avg(price) price
                                                     from (select a.itemname, sum(a.sourcecount * b.buyprice) price
                                                           from itemrecipe a
                                                                    inner join item b on a.sourcename = b.itemname
                                                           group by a.itemname, a.type) z
                                                     group by z.itemname) z on a.itemname = z.itemname
                           set a.makeprice=z.price');
        DB::connection('mysql' . $dbIndex)->update('update item a set a.makeprice10 = ifnull((select avg(b.price) from (select sum(c.sourcecount * d.buyprice10) price from itemrecipe c inner join item d on c.itemname = a.itemname and c.sourcename = d.itemname group by c.type) b), 0)');

        // 更新item.costprice/10
        DB::connection('mysql' . $dbIndex)->update('update item a set a.costprice = if(a.buyprice > a.makeprice, a.buyprice, a.makeprice)');
        DB::connection('mysql' . $dbIndex)->update('update item a set a.costprice10 = if(a.buyprice10 > a.makeprice10, a.buyprice10, a.makeprice10)');

        // 更新item.sellprice/10, minsellprice/10, maxsellprice/10
        DB::connection('mysql' . $dbIndex)->update('update item a left join (select a.itemname, round(avg(c.price)) avgprice, min(c.price) minprice, max(c.price) maxprice
                                                     from item a
                                                     inner join sellhistory c on a.itemname = c.itemname
                                                     group by a.itemname) z on a.itemname = z.itemname
                           set a.sellprice=ifnull(z.avgprice, 0),
                               a.minsellprice=ifnull(z.minprice, 0),
                               a.maxsellprice=ifnull(z.maxprice, 0)');
        DB::connection('mysql' . $dbIndex)->update('update item a set
                               a.sellprice10 = ifnull((select avg(b.price) from (select c.price from sellhistory c where c.itemname = a.itemname and selltime > unix_timestamp()-10*24*3600) b), 0),
                               a.minsellprice10 = ifnull((select min(b.price) from (select c.price from sellhistory c where c.itemname = a.itemname and selltime > unix_timestamp()-10*24*3600) b), 0),
                               a.maxsellprice10 = ifnull((select max(b.price) from (select c.price from sellhistory c where c.itemname = a.itemname and selltime > unix_timestamp()-10*24*3600) b), 0)');

        // 更新dealhistory.price
        DB::connection('mysql' . $dbIndex)->update('update dealhistory a inner join (select a.id, a.itemname, max(b.stacksize) stacksize
                                                            from dealhistory a
                                                                     inner join sellhistory b on a.itemname = b.itemname
                                                                         and abs(a.totalprice / 0.95 - b.stackprice) / b.stackprice < 0.20
                                                            where a.issuccess = 1 and a.price = 0
                                                            group by a.id, a.itemname) z on a.id = z.id
                           set a.price=ifnull(a.totalprice / 0.95 / z.stacksize, a.totalprice)');
        // 找不到分组价格
        $dealNotFoundCount = DB::connection('mysql' . $dbIndex)->selectOne('select count(1) c from dealhistory where issuccess = 1 and price = 0')->c;
        if ($dealNotFoundCount > 0) {
            DB::connection('mysql' . $dbIndex)->update('update dealhistory set price = totalprice where issuccess = 1 and price = 0');
        }

        // 更新item.dealprice/10
        DB::connection('mysql' . $dbIndex)->update('update item a left join (select a.itemname, round(avg(c.price)) avgprice
                                                     from item a
                                                     inner join dealhistory c on a.itemname = c.itemname and c.issuccess=1
                                                     group by a.itemname) z on a.itemname = z.itemname
                           set a.dealprice=ifnull(z.avgprice, 0)');
        DB::connection('mysql' . $dbIndex)->update('update item a set a.dealprice10 = ifnull((select round(avg(b.price)) from (select c.price from dealhistory c where c.itemname = a.itemname and c.issuccess = 1 and dealtime > unix_timestamp()-10*24*3600) b), 0)');

        // 更新item.dealcount/10, sellcount/10, dealproportion/10
        DB::connection('mysql' . $dbIndex)->update('update item a left join (select a.itemname, sum(issuccess) dealcount, count(1) sellcount
                                                     from item a
                                                     inner join dealhistory c on a.itemname = c.itemname
                                                     group by a.itemname) z on a.itemname = z.itemname
                           set a.dealcount=ifnull(z.dealcount, 0), a.sellcount=ifnull(z.sellcount, 0), a.dealproportion=ifnull(z.dealcount/z.sellcount, 0)');
        DB::connection('mysql' . $dbIndex)->update('update item a
                           set a.dealcount10 = ifnull((select sum(c.issuccess) from (select b.issuccess from dealhistory b where b.itemname = a.itemname and dealtime > unix_timestamp() - 10 * 24 * 3600) c), 0),
                           a.sellcount10 = ifnull((select count(1) from dealhistory b where b.itemname = a.itemname and dealtime > unix_timestamp() - 10 * 24 * 3600), 0),
                           a.dealproportion10 = ifnull((select sum(c.issuccess) / count(1) from (select b.issuccess from dealhistory b where b.itemname = a.itemname and dealtime > unix_timestamp() - 10 * 24 * 3600) c), 0)');

        // 更新item.lowestprice/10, profit/10, profitrate/10
        DB::connection('mysql' . $dbIndex)->update('update item
                            set lowestprice= if(dealproportion = 0, 0, (costprice + vendorprice * 0.15 / dealproportion) / 0.95),
                                profit=(sellprice * 0.95 - costprice) * dealproportion - vendorprice * 0.15 * (1 - dealproportion),
                                profitrate=if(costprice=0,0, ((sellprice * 0.95 - costprice) * dealproportion - vendorprice * 0.15 * (1 - dealproportion)) / costprice),
                                lowestprice10= if(dealproportion10 = 0, 0, (costprice10 + vendorprice * 0.15 / dealproportion10) / 0.95),
                                profit10=(sellprice10 * 0.95 - costprice10) * dealproportion10 - vendorprice * 0.15 * (1 - dealproportion10),
                                profitrate10=if(costprice10=0,0, ((sellprice10 * 0.95 - costprice10) * dealproportion10 - vendorprice * 0.15 * (1 - dealproportion10)) / costprice10)');

        // 更新item.totalprofit, profitproportion
        DB::connection('mysql' . $dbIndex)->update('update item a
                                inner join (select sum(profit * dealcount) sumprofit from item) b on 1 = 1
                            set totalprofit     = profit * dealcount,
                                profitproportion= profit * dealcount / b.sumprofit');

        // 更新item.groupdealproportion, groupprofitproportion
        DB::connection('mysql' . $dbIndex)->update("update item a left join (select `group`, sum(dealcount) groupdealcount, sum(profit * item.dealcount) grouptotalprofit
                                                    from item
                                                    where `group` <> ''
                                                    group by `group`) z on a.`group` = z.`group`
                           set a.groupdealproportion=if(z.groupdealcount=0, 0, ifnull(a.dealcount / z.groupdealcount, 0)),
                               a.groupprofitproportion= if(z.grouptotalprofit=0, 0, ifnull(a.profit * a.dealcount / z.grouptotalprofit, 0))");

//        echo sprintf("Analyse Finished.\nDeal not found: %s\n\n", $dealNotFoundCount);

//        // 输出最有价值的物品
//        echo "Most Valuable Goods: \n";
//        if ($dbIndex == 1) {
//            echo 'FilePath: d:\Games\Wow\WTF\Account\13382850839\SavedVariables\Auctionator.lua' . "\n";
//        } else {
//            echo 'FilePath: d:\Games\Wow\WTF\Account\13961229851\SavedVariables\Auctionator.lua' . "\n";
//        }
//        $mostValuable = ItemModel::on('mysql' . $dbIndex)->where([['profit10', '>', 100000], ['dealcount10', '>', 30], ['sort', '>', 0], 'category' => '珠宝'])
//            ->orderBy('dealcount10', 'desc')->pluck('itemname');
//
//        $quickest = ItemModel::on('mysql' . $dbIndex)->where([['dealproportion10', '>', 0.3], ['dealcount10', '>', 20], ['sort', '>', 0], 'category' => '珠宝'])
//            ->whereNotIn('itemname', $mostValuable)->orderBy('dealcount10', 'desc')->pluck('itemname');
//
//        $mostProfitRate = ItemModel::on('mysql' . $dbIndex)->where([['profitrate10', '>', 0.4], ['dealcount10', '>', 10], ['sort', '>', 0], 'category' => '珠宝'])
//            ->whereNotIn('itemname', $mostValuable)->whereNotIn('itemname', $quickest)->orderBy('profitrate10', 'desc')->pluck('itemname');
//
//        echo "    {\n";
//        echo "        [\"items\"] = {\n";
//        for ($i = 1; $i <= count($mostValuable); $i++) {
//            echo sprintf("            \"%s\", -- [%s]\n", $mostValuable[$i - 1], $i);
//        }
//        echo "        },\n";
//        echo "        [\"name\"] = \"0P10C30置顶\",\n";
//        echo "        [\"isSorted\"] = true,\n";
//        echo "    }, -- [2]\n";
//
//        echo "    {\n";
//        echo "        [\"items\"] = {\n";
//        for ($i = 1; $i <= count($quickest); $i++) {
//            echo sprintf("            \"%s\", -- [%s]\n", $quickest[$i - 1], $i);
//        }
//        echo "        },\n";
//        echo "        [\"name\"] = \"1DR30C20快速\",\n";
//        echo "        [\"isSorted\"] = true,\n";
//        echo "    }, -- [3]\n";
//
//        echo "    {\n";
//        echo "        [\"items\"] = {\n";
//        for ($i = 1; $i <= count($mostProfitRate); $i++) {
//            echo sprintf("            \"%s\", -- [%s]\n", $mostProfitRate[$i - 1], $i);
//        }
//        echo "        },\n";
//        echo "        [\"name\"] = \"2PR40C10钱多\",\n";
//        echo "        [\"isSorted\"] = true,\n";
//        echo "    }, -- [4]\n";
//
//        echo "\n";
    }
}
