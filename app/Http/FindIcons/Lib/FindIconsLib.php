<?php

namespace App\Http\FindIcons\Lib;

use GuzzleHttp\Client;

class FindIconsLib
{
    static function download($url, $listCount)
    {
        $client = new Client();
        $urls = [];
        for ($listIndex = 1; $listIndex <= $listCount; $listIndex++) {
            $listUrl = $url . ($listIndex == 1 ? '' : "/$listIndex");
            $res = $client->request('get', $listUrl);

            $listContent = $res->getBody();
            $pattern = '/(?<=<li class="items">)[\w\W]*?(?=<\/li>)/';
            preg_match_all($pattern, $listContent, $matches);
            foreach ($matches[0] as $match) {
                $pattern2 = '/href="\/icon\/([^"]*)"/';
                preg_match_all($pattern2, $match, $matches2);
                $urls[] = $matches2[1][0];
            }
        }
        foreach ($urls as $url) {
//            https://findicons.com/icon/download/direct/21760/html_file/0/ico
            $nurl = sprintf('https://findicons.com/icon/download/direct/%s/0/ico', $url);
            $client->request('get', $nurl, ['sink' => storage_path('findicons/' . str_replace('/', '_', $url) . '.ico')]);
            echo $url;
            echo "\n";
            echo str_replace('/', '_', $url);
            echo "\n";
        }
//        echo json_encode($urls, JSON_PRETTY_PRINT);
//        echo "\n";
//        echo count($urls);
    }
}
