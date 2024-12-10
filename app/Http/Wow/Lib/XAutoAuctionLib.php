<?php

namespace App\Http\Wow\Lib;

use Illuminate\Support\Facades\DB;

class XAutoAuctionLib
{
    const XItemInfoListImportPrefix = 'XItemInfoListImport = ';
    const XSellExportPrefix = 'XSellExport = ';
    const XBuyExportPrefix = 'XBuyExport = ';

    const TaxRate = 0.95;
    const FeeRate = 0.15;

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

            while (($line = fgets($inFile)) !== false) {
                if (!empty($res = self::processLineExport($line, self::XSellExportPrefix))) {
                    $sellStr = $res;
                }
                if (!empty($res = self::processLineExport($line, self::XBuyExportPrefix))) {
                    $buyStr = $res;
                }
            }

            rewind($inFile);

            // 处理数据
            $summary = '导入摘要:';
            if (!empty($sellStr)) {
                $list = json_decode($sellStr);
                $items = [];
                foreach ($list as $item) {
                    $items[] = [
                        'itemname' => $item->itemname,
                        'issuccess' => $item->issuccess ? 1 : 0,
                        'price' => $item->price,
                        'count' => $item->count,
                        'dealtime' => $item->time,
                        'dealdate' => date('Y-m-d', $item->time),
                        'createtime' => time()
                    ];
                }
                if (!empty($items)) {
                    $connection->table('imp_sellhistory')->insert($items);
                    $summary .= '    出售: ' . count($items);
                }
            }
            if (!empty($buyStr)) {
                $list = json_decode($buyStr);
                $items = [];
                foreach ($list as $item) {
                    $items[] = [
                        'itemname' => $item->itemname,
                        'price' => $item->price,
                        'count' => $item->count,
                        'buytime' => $item->time,
                        'buydate' => date('Y-m-d', $item->time),
                        'createtime' => time()
                    ];
                }
                if (!empty($items)) {
                    $connection->table('imp_buyhistory')->insert($items);
                    $summary .= '    购买: ' . count($items);
                }
            }

            self::analyse($connection);

            $connection->commit();
            $itemList = $connection->table('dat_item')->get();
            $itemMap = json_decode(json_encode($itemList), true);
            foreach ($itemMap as &$item) {
                foreach ($item as $k => $v) {
                    $item[$k] = $v . '';
                }
            }
            unset($item);

            if ($inFile && $outFile) {
                while (($line = fgets($inFile)) !== false) {
                    self::processLineImport($line, self::XItemInfoListImportPrefix, $itemMap);
                    self::processLineExport($line, self::XSellExportPrefix);
                    self::processLineExport($line, self::XBuyExportPrefix);
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

    public static function analyse($connection)
    {
        $feeRate = self::FeeRate;
        $taxRate = self::TaxRate;

        // init
        $connection->update('insert into imp_buyhistory_his
                               select * from imp_buyhistory
                                 where buytime < unix_timestamp(curdate()) - 15*24*3600');
        $connection->update('delete from imp_buyhistory
                               where buytime < unix_timestamp(curdate()) - 15*24*3600');
        $connection->update('insert into imp_sellhistory_his
                               select * from imp_sellhistory
                                 where dealtime < unix_timestamp(curdate()) - 15*24*3600');
        $connection->update('delete from imp_sellhistory
                               where dealtime < unix_timestamp(curdate()) - 15*24*3600');

        // buyprice
        $connection->update('update dat_item a left join
                                 (select itemname, sum(price*count)/sum(count) buyprice
                                  from imp_buyhistory
                                  where price > 0 and count > 0
                                  and buytime > unix_timestamp(curdate()) - 3*24*3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.buyprice=if(b.buyprice is null, a.buyprice, b.buyprice*0.9+a.buyprice*0.1)');

        // dealprice, dealcount, dealbatchcount
        $connection->update('update dat_item a left join
                                 (select itemname, sum(price*count)/sum(count) dealprice, sum(count) dealcount, count(1) dealbatchcount
                                  from imp_sellhistory
                                  where price > 0 and count > 0
                                  and issuccess = 1
                                  and dealtime > unix_timestamp(curdate()) - 3*24*3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.dealprice=ifnull(b.dealprice,0), a.dealcount=ifnull(b.dealcount,0), a.dealbatchcount=ifnull(b.dealbatchcount,0)');

        // sellcount, sellbatchcount
        $connection->update('update dat_item a left join
                                 (select itemname, sum(count) sellcount, count(1) sellbatchcount
                                  from imp_sellhistory
                                  where count > 0
                                  and dealtime > unix_timestamp(curdate()) - 3*24*3600
                                  group by itemname) b on b.itemname = a.itemname
                             set a.sellcount=ifnull(b.sellcount,0), a.sellbatchcount=ifnull(b.sellbatchcount,0)');

        // dealrate
        $connection->update('update dat_item a
                             set dealrate=if(dealbatchcount = 0, 99, sellbatchcount / dealbatchcount)');

        // statistics
        $connection->delete('delete from sta_dealcount');
        $connection->update("insert into sta_dealcount
                             select z.dealdate, x.sourcename, count(1) sellcount, sum(z.issuccess) dealcount,
                                    sum(if(z.issuccess=1, z.price, 0)) income, sum(z.fee)
                               from (select z.*, if(z.issuccess=0, z.count*y.vendorprice*${feeRate}, 0) fee from imp_sellhistory z inner join dat_item y on z.itemname=y.itemname) z
                               inner join dat_itemrecipe x on z.itemname = x.itemname
                             group by z.dealdate, x.sourcename");

        $connection->delete('delete from sta_dealjewcount');
        $connection->update("insert into sta_dealjewcount(日期, 星期) 
                                select from_unixtime(unix_timestamp()-day*24*3600, '%Y-%m-%d') d,
                                  from_unixtime(unix_timestamp()-day*24*3600,'%W') w from dat_days");
        $connection->update("update sta_dealjewcount a set
                               收入=(select ifnull(sum(b.income)/10000,0) from sta_dealcount b where a.日期=b.dealdate),
                               成交=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate),
                               手续=(select ifnull(sum(b.fee)/10000,0) from sta_dealcount b where a.日期=b.dealdate),
                               出售=(select ifnull(sum(b.sellcount),0) from sta_dealcount b where a.日期=b.dealdate),
                               赤玉=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='赤玉石'),
                               紫黄=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='紫黄晶'),
                               王者=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='王者琥珀'),
                               祖尔=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='祖尔之眼'),
                               巨锆=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='巨锆石'),
                               恐惧=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='恐惧石'),
                               血玉=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='血玉石'),
                               帝黄=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='帝黄晶'),
                               秋色=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='秋色石'),
                               森林=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='森林翡翠'),
                               天蓝=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='天蓝石'),
                               曙光=(select ifnull(sum(b.dealcount),0) from sta_dealcount b where a.日期=b.dealdate and b.sourcename='曙光猫眼石')
        ");

        $connection->update("update sta_dealjewcount a
                             set 成本=
                                     ifnull((select ((select b.buyprice from dat_item b where b.itemname = '赤玉石') * a.赤玉
                                         + (select b.buyprice from dat_item b where b.itemname = '紫黄晶') * a.紫黄
                                         + (select b.buyprice from dat_item b where b.itemname = '王者琥珀') * a.王者
                                         + (select b.buyprice from dat_item b where b.itemname = '祖尔之眼') * a.祖尔
                                         + (select b.buyprice from dat_item b where b.itemname = '巨锆石') * a.巨锆
                                         + (select b.buyprice from dat_item b where b.itemname = '恐惧石') * a.恐惧
                                         + (select b.buyprice from dat_item b where b.itemname = '血玉石') * a.血玉
                                         + (select b.buyprice from dat_item b where b.itemname = '帝黄晶') * a.帝黄
                                         + (select b.buyprice from dat_item b where b.itemname = '秋色石') * a.秋色
                                         + (select b.buyprice from dat_item b where b.itemname = '森林翡翠') * a.森林
                                         + (select b.buyprice from dat_item b where b.itemname = '天蓝石') * a.天蓝
                                         + (select b.buyprice from dat_item b where b.itemname = '曙光猫眼石') * a.曙光) / 10000),0)");

        $connection->update("update sta_dealjewcount
                             set 利润=收入-成本-手续,
                                 利润率=if(成本=0, 0, (收入 - 成本 - 手续) / 成本 * 100),
                                 成交率=if(出售=0, 0, round(成交/出售*100))");

        $connection->commit();
    }

}
