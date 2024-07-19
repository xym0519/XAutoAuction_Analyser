<?php

namespace App\Http\Wow\Lib;

class CommonLib
{
    public static function luaFile2Json($filePath)
    {
        $handle = fopen($filePath, 'r');
        $result = [];
        if ($handle) {
            $level = 0;
            $levelKeys = [];
            while (($line = fgets($handle)) !== false) {
                $isArrayItem = false;
                if (preg_match("/-- \[\d+]/", $line)) {
                    $isArrayItem = true;
                }
                if (str_contains($line, '--')) {
                    $line = substr($line, 0, strpos($line, '--'));
                }
                $line = trim($line, " \t,\r\n");
                if (empty($line)) {
                    continue;
                }
                if (preg_match('/^(.*)=(.*)$/', $line, $match)) {
                    $k = trim($match[1], " \t[]\"");
                    $v = trim($match[2], " \t\"");
                    $item = &$result;
                    for ($i = 0; $i < $level; $i++) {
                        $item = &$item[$levelKeys[$i]];
                    }
                    if ($v == '{') {
                        $item[$k] = [];
                        $levelKeys[$level] = $k;
                        $level++;
                    } else {
                        $item[$k] = $v;
                    }
                    unset($item);
                    continue;
                }
                if ($line == '{') {
                    $item = &$result;
                    for ($i = 0; $i < $level; $i++) {
                        $item = &$item[$levelKeys[$i]];
                    }
                    if (array_key_exists($level, $levelKeys)) {
                        $levelKeys[$level]++;
                    } else {
                        $levelKeys[$level] = 0;
                    }
                    $item[$levelKeys[$level]] = [];
                    $level++;
                    unset($item);
                    continue;
                }
                if ($line == '}') {
                    unset($levelKeys[$level]);
                    $level--;
                    continue;
                }
                if ($isArrayItem) {
                    $v = trim($line, " \t\"");
                    $item = &$result;
                    for ($i = 0; $i < $level; $i++) {
                        $item = &$item[$levelKeys[$i]];
                    }
                    if (array_key_exists($level, $levelKeys)) {
                        $levelKeys[$level]++;
                    } else {
                        $levelKeys[$level] = 0;
                    }
                    $item[$levelKeys[$level]] = $v;
                    unset($item);
                }
            }
            fclose($handle);
        }
        return $result;
    }
}