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

        DB::connection('mysql' . $dbIndex)->beginTransaction();

        $inFile = fopen($filePath, 'r');
        $outFile = fopen($tempPath, 'w');

        $sellStr = '';
        $buyStr = '';
        $scanStr = '';
        if ($inFile && $outFile) {
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

            $itemList = DB::connection('mysql' . $dbIndex)->table('dat_item')->get();
            $itemMap = json_decode(json_encode($itemList), true);
            foreach ($itemMap as &$item) {
                foreach ($item as $k => $v) {
                    $item[$k] = $v . '';
                }
                ksort($item);
            }


            if ($inFile && $outFile) {
                while (($line = fgets($inFile)) !== false) {
                    self::processLineImport($line, self::XAuctionInfoListImportPrefix, $itemMap);

                    if (!empty($res = self::processLineExport($line, self::XSellExportPrefix))) {
                        $sellStr = $res;
                    }
                    if (!empty($res = self::processLineExport($line, self::XBuyExportPrefix))) {
                        $buyStr = $res;
                    }
                    if (!empty($res = self::processLineExport($line, self::XScanExportPrefix))) {
                        $scanStr = $res;
                    }
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
        if (!empty($sellStr)) {
            $sellList = json_decode($sellStr);
            echo "selllist:\n";
            echo json_encode($sellList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        if (!empty($buyStr)) {
            echo "buylist:\n";
            $buyList = json_decode($buyStr);
            echo json_encode($buyList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        if (!empty($scanStr)) {
            echo "scanlist:\n";
            $scanList = json_decode($scanStr);
            echo json_encode($scanList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
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
}
