<?php

namespace App\Http\Wow\Lib;

use Illuminate\Support\Facades\DB;

class XAutoAuctionLib
{
    const XAuctionInfoListImportPrefix = 'XAuctionInfoListImport = ';
    const XSellExportPrefix = 'XSellExport = ';
    const XBuyExportPrefix = 'XBuyExport = ';
    const XScanExportPrefix = 'XScanExport = ';

    static function process($filePath, $dbIndex)
    {
        $tempPath = $filePath . '.tmp' . date('Ymdhis');       // 创建一个临时文件路径

        $connection = DB::connection('mysql' . $dbIndex);
        $connection->beginTransaction();

        $inFile = fopen($filePath, 'r');
        $outFile = fopen($tempPath, 'w');

        if ($inFile && $outFile) {
            $sellStr = '';
            $buyStr = '';
            $scanStr = '';

            while (($line = fgets($inFile)) !== false) {
                if (!empty($res = self::processLineExport($line, self::XSellExportPrefix))) {
                    $sellStr = $res;
                }
                if (!empty($res = self::processLineExport($line, self::XBuyExportPrefix))) {
                    $buyStr = $res;
                }
                if (!empty($res = self::processLineExport($line, self::XScanExportPrefix))) {
                    $scanStr = $res;
                }
            }

            rewind($inFile);

            // 处理数据
            $summary = '导入摘要:';
            if (!empty($scanStr)) {
                $scanList = json_decode($scanStr);
                $scanItems = [];
                foreach ($scanList as $itemName => $item) {
                    if (!ItemLib::checkItem($itemName, $connection)) {
                        $connection->table('dat_item')->insert([
                            'itemname' => $itemName,
                            'vendorprice' => $item->vendorprice,
                            'category' => $item->category,
                            'class' => $item->class
                        ]);
                    } else {
                        $connection->table('dat_item')->where('itemname', $itemName)->update([
                            'vendorprice' => $item->vendorprice,
                            'category' => $item->category,
                            'class' => $item->class
                        ]);
                    }

                    foreach ($item->list as $record) {
                        $scanItems[] = [
                            'itemname' => $itemName,
                            'scantime' => $record->time,
                            'price' => $record->price,
                            'createtime' => time()
                        ];
                    }
                }
                if (!empty($scanItems)) {
                    $connection->table('imp_scanhistory')->insert($scanItems);
                    $summary .= '    扫描: ' . count($scanItems);
                }
            }
            if (!empty($sellStr)) {
                $sellList = json_decode($sellStr);
                foreach ($sellList as $item) {
                    $item->dealtime = $item->time;
                    $item->dealdate = date('Y-m-d', $item->time);
                    $item->createtime = time();
                }
                if (!empty($sellList)) {
                    $connection->table('imp_sellhistory')->insert($sellList);
                    $summary .= '    出售: ' . count($sellList);
                }
            }
            if (!empty($buyStr)) {
                $buyList = json_decode($buyStr);
                foreach ($buyList as $item) {
                    $item->buytime = $item->time;
                    $item->buydate = date('Y-m-d', $item->time);
                    $item->createtime = time();
                }
                if (!empty($buyList)) {
                    $connection->table('imp_buyhistory')->insert($buyList);
                    $summary .= '    购买: ' . count($buyList);
                }
            }

            self::analyse($connection);

            $itemList = DB::connection('mysql' . $dbIndex)->table('dat_item')->get();
            $itemMap = json_decode(json_encode($itemList), true);
            foreach ($itemMap as &$item) {
                foreach ($item as $k => $v) {
                    $item[$k] = $v . '';
                }
            }
            unset($item);

            if ($inFile && $outFile) {
                while (($line = fgets($inFile)) !== false) {
                    self::processLineImport($line, self::XAuctionInfoListImportPrefix, $itemMap);
                    self::processLineExport($line, self::XSellExportPrefix);
                    self::processLineExport($line, self::XBuyExportPrefix);
                    self::processLineExport($line, self::XScanExportPrefix);
                    fwrite($outFile, $line);
                }
            }

            // 关闭文件
            fclose($inFile);
            fclose($outFile);

            // 删除原文件并将临时文件重命名为原文件名
            unlink($filePath);
            rename($tempPath, $filePath);
        }
        $connection->commit();

        echo $summary;
    }

    private static function processLineImport(&$line, $prefix, $content)
    {
        if (mb_substr($line, 0, mb_strlen($prefix)) === $prefix) {
            $result = mb_substr($line, mb_strlen($prefix));
            $result = trim($result, "\" \t\n\r\0\x0B");
            $result = str_replace("\\", '', $result);
            if (empty($result) && !empty($content)) {
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                $content = str_replace('"', "\\\"", $content);
                $line = $prefix . '"' . $content . '"' . PHP_EOL;
                return true;
            }
        }
        return false;
    }

    private static function processLineExport(&$line, $prefix)
    {
        $result = '';
        if (mb_substr($line, 0, mb_strlen($prefix)) === $prefix) {
            $result = mb_substr($line, mb_strlen($prefix));
            $result = trim($result, "\" \t\n\r\0\x0B");
            $result = str_replace("\\", '', $result);
            $line = $prefix . '""' . PHP_EOL;
        }
        return $result;
    }

    private static function analyse($connection)
    {
        // scanprice, minscanprice, maxscanprice
        $connection->update('update dat_item a inner join
                                 (select itemname, avg(price) scanprice, min(price) minscanprice, max(price) maxscanprice
                                  from imp_scanhistory
                                  where price > 0
                                  group by itemname) b on b.itemname = a.itemname
                             set a.scanprice=b.scanprice, a.minscanprice=b.minscanprice, a.maxscanprice=b.maxscanprice');

        // scanprice10, minscanprice10, maxscanprice10
        $connection->update('update dat_item a inner join
                                 (select itemname, avg(price) scanprice, min(price) minscanprice, max(price) maxscanprice
                                  from imp_scanhistory
                                  where price > 0
                                    and scantime >= unix_timestamp() - 10 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.scanprice10=b.scanprice, a.minscanprice10=b.minscanprice, a.maxscanprice10=b.maxscanprice');

        // scanprice30, minscanprice30, maxscanprice30
        $connection->update('update dat_item a inner join
                                 (select itemname, avg(price) scanprice, min(price) minscanprice, max(price) maxscanprice
                                  from imp_scanhistory
                                  where price > 0
                                    and scantime >= unix_timestamp() - 30 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.scanprice30=b.scanprice, a.minscanprice30=b.minscanprice, a.maxscanprice30=b.maxscanprice');

        // buyprice
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) buyprice
                                  from imp_buyhistory
                                  where price > 0 and count > 0
                                  group by itemname) b on b.itemname = a.itemname
                             set a.buyprice=b.buyprice');

        // buyprice10
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) buyprice
                                  from imp_buyhistory
                                  where price > 0 and count > 0
                                    and buytime >= unix_timestamp() - 10 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.buyprice10=b.buyprice');

        // buyprice30
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) buyprice
                                  from imp_buyhistory
                                  where price > 0 and count > 0
                                    and buytime >= unix_timestamp() - 30 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.buyprice30=b.buyprice');

        // default buyprice/10/30
        $connection->update('update dat_item a
                             set a.buyprice=if(a.buyprice = 0, a.scanprice, a.buyprice),
                                 a.buyprice10=if(a.buyprice10 = 0, a.scanprice10, a.buyprice10),
                                 a.buyprice30=if(a.buyprice30 = 0, a.scanprice30, a.buyprice30)');

        // makeprice, makeprice10, makeprice30
        $connection->update('update dat_item a inner join
                                 (select z.itemname, max(price) price, max(price10) price10, max(price30) price30
                                  from (select a.itemname,
                                               sum(a.sourcecount * b.buyprice)   price,
                                               sum(a.sourcecount * b.buyprice10) price10,
                                               sum(a.sourcecount * b.buyprice30) price30
                                        from dat_itemrecipe a
                                                 inner join dat_item b on a.sourcename = b.itemname
                                        group by a.itemname, a.type) z
                                  group by z.itemname) z on a.itemname = z.itemname
                             set a.makeprice=z.price,
                                 a.makeprice10=z.price10,
                                 a.makeprice30=z.price30');

        // costprice, costprice10, costprice30
        $connection->update('update dat_item a
                             set a.costprice=if(a.buyprice > a.makeprice, a.buyprice, a.makeprice),
                                 a.costprice10=if(a.buyprice10 > a.makeprice10, a.buyprice10, a.makeprice10),
                                 a.costprice30=if(a.buyprice30 > a.makeprice30, a.buyprice30, a.makeprice30)');

        // dealprice, dealcount
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) dealprice, sum(count) dealcount
                                  from imp_sellhistory
                                  where price > 0 and count > 0
                                  and issuccess = 1
                                  group by itemname) b on b.itemname = a.itemname
                             set a.dealprice=b.dealprice, a.dealcount=b.dealcount');

        // dealprice10, dealcount10
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) dealprice, sum(count) dealcount
                                  from imp_sellhistory
                                  where price > 0 and count > 0
                                    and issuccess = 1
                                    and dealtime >= unix_timestamp() - 10 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.dealprice10=b.dealprice, a.dealcount10=b.dealcount');

        // dealprice30, dealcount30
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(price*count)/sum(count) dealprice, sum(count) dealcount
                                  from imp_sellhistory
                                  where price > 0 and count > 0
                                    and issuccess = 1
                                    and dealtime >= unix_timestamp() - 30 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.dealprice30=b.dealprice, a.dealcount30=b.dealcount');

        // sellcount
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(count) sellcount
                                  from imp_sellhistory
                                  where count > 0
                                  group by itemname) b on b.itemname = a.itemname
                             set a.sellcount=b.sellcount');

        // sellcount10
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(count) sellcount
                                  from imp_sellhistory
                                  where count > 0
                                    and dealtime >= unix_timestamp() - 10 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.sellcount10=b.sellcount');

        // sellcount30
        $connection->update('update dat_item a inner join
                                 (select itemname, sum(count) sellcount
                                  from imp_sellhistory
                                  where count > 0
                                    and dealtime >= unix_timestamp() - 30 * 24 * 3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.sellcount30=b.sellcount');

        // dealrate, dealrate10, dealrate30
        $connection->update('update dat_item a
                             set dealrate=if(dealcount = 0, 99, sellcount / dealcount),
                                 dealrate10=if(dealcount10 = 0, 99, sellcount10 / dealcount10),
                                 dealrate30=if(dealcount30 = 0, 99, sellcount30 / dealcount30)');

        // lowestprice, lowestprice10, lowestprice30, profit, profit10, profit30
        $connection->update('update dat_item a
                             set a.lowestprice=if(a.dealrate = 0, 9999999, (a.costprice + a.vendorprice * 0.15 * a.dealrate) / 0.95),
                                 a.lowestprice10=if(a.dealrate10 = 0, 9999999, (a.costprice10 + a.vendorprice * 0.15 * a.dealrate10) / 0.95),
                                 a.lowestprice30=if(a.dealrate30 = 0, 9999999, (a.costprice30 + a.vendorprice * 0.15 * a.dealrate30) / 0.95),
                                 a.profit=if(a.dealrate = 0, 0, (a.dealprice - a.costprice) / a.dealrate - a.vendorprice * 0.15 * (1 - 1 / a.dealrate)),
                                 a.profit10=if(a.dealrate = 0, 0, (a.dealprice10 - a.costprice10) / a.dealrate10 - a.vendorprice * 0.15 * (1 - 1 / a.dealrate10)),
                                 a.profit30=if(a.dealrate = 0, 0, (a.dealprice30 - a.costprice30) / a.dealrate30 - a.vendorprice * 0.15 * (1 - 1 / a.dealrate30))');

        // profitrate, profitrate10, profitrate30
        $connection->update('update dat_item a
                             set a.profitrate=if(a.costprice = 0, 0, a.profit / a.costprice),
                                 a.profitrate10=if(a.costprice10 = 0, 0, a.profit10 / a.costprice10),
                                 a.profitrate30=if(a.costprice30 = 0, 0, a.profit30 / a.costprice30)');

        // totalprofit, totalprofit10, totalprofit30, profitproportion, profitproportion10, profitproportion30
        $connection->update('update dat_item a inner join
                                 (select sum(profit * dealcount)     sumprofit,
                                         sum(profit10 * dealcount10) sumprofit10,
                                         sum(profit30 * dealcount30) sumprofit30
                                  from dat_item) b
                             set a.totalprofit=a.profit * a.dealcount,
                                 a.totalprofit10=a.profit10 * a.dealcount10,
                                 a.totalprofit30=a.profit30 * a.dealcount30,
                                 a.profitproportion=if(b.sumprofit = 0, 0, a.profit * a.dealcount / b.sumprofit),
                                 a.profitproportion10=if(b.sumprofit10 = 0, 0, a.profit10 * a.dealcount10 / b.sumprofit10),
                                 a.profitproportion30=if(b.sumprofit30 = 0, 0, a.profit30 * a.dealcount30 / b.sumprofit30)');

        // groupdealproportion, groupdealproportion10, groupdealproportion30, groupprofitproportion, groupprofitproportion10, groupprofitproportion30
        $connection->update('update dat_item a left join
                                 (select `group`,
                                         sum(dealcount)              groupdealcount,
                                         sum(dealcount10)            groupdealcount10,
                                         sum(dealcount30)            groupdealcount30,
                                         sum(profit * dealcount)     grouptotalprofit,
                                         sum(profit10 * dealcount10) grouptotalprofit10,
                                         sum(profit30 * dealcount30) grouptotalprofit30
                                  from dat_item
                                  where `group` <> \'\'
                                  group by `group`) z on a.`group` = z.`group`
                             set a.groupdealproportion=if(z.groupdealcount = 0, 0, ifnull(a.dealcount / z.groupdealcount, 0)),
                                 a.groupdealproportion10=if(z.groupdealcount10 = 0, 0, ifnull(a.dealcount10 / z.groupdealcount10, 0)),
                                 a.groupdealproportion30=if(z.groupdealcount30 = 0, 0, ifnull(a.dealcount30 / z.groupdealcount30, 0)),
                                 a.groupprofitproportion=if(z.grouptotalprofit = 0, 0, ifnull(a.profit * a.dealcount, 0)),
                                 a.groupprofitproportion10=if(z.grouptotalprofit10 = 0, 0, ifnull(a.profit10 * a.dealcount10, 0)),
                                 a.groupprofitproportion30=if(z.grouptotalprofit30 = 0, 0, ifnull(a.profit30 * a.dealcount30, 0))');

        // statistics
        $connection->update('truncate table sta_dealcount');

        $connection->update('insert into sta_dealcount
                             select z.dealdate, x.sourcename, count(1) c from imp_sellhistory z
                             inner join dat_item y on z.itemname=y.itemname and y.category=\'珠宝\'
                             inner join dat_itemrecipe x on z.itemname = x.itemname
                             where z.issuccess = 1
                             group by z.dealdate, x.sourcename');

        $connection->update('truncate table sta_dealjewcount');

        $connection->update('insert into sta_dealjewcount
                             select substr(y.d, 6) d, substr(y.w, 1,3) w, round(y.income/10000) i, y.success s,
                                    if(y.total=0, 0, round(y.success/y.total*100)) r, y.total t,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'赤玉石\'), 0) 赤玉,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'紫黄晶\'), 0) 紫黄,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'王者琥珀\'), 0) 王者,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'祖尔之眼\'), 0) 祖尔,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'巨锆石\'), 0) 巨锆,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'恐惧石\'), 0) 恐惧,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'血玉石\'), 0) 血玉,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'帝黄晶\'), 0) 帝黄,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'秋色石\'), 0) 秋色,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'森林翡翠\'), 0) 森林,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'天蓝石\'), 0) 天蓝,
                                    ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename=\'曙光猫眼石\'), 0) 曙光
                             from(
                                 select z.d, z.w, ifnull(sum(b.price*b.count),0) income,
                                            ifnull(count(issuccess),0) total,
                                            ifnull(sum(issuccess),0) success
                                     from (select from_unixtime(unix_timestamp()-day*24*3600, \'%Y-%m-%d\') d,
                                                  from_unixtime(unix_timestamp()-day*24*3600,\'%W\') w from dat_days) z
                                              left join imp_sellhistory b on dealdate = z.d
                                     group by z.d, z.w) y
                             order by y.d desc');
    }

}
