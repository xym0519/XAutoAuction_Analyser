<?php

namespace IconGenerate;

use App\Http\IconGenerate\Lib\IconGenerateLib;
use TestCase;

class IconGenerateTest extends TestCase
{
    public function test()
    {
        IconGenerateLib::generate('#ff#1890ff', '#1890ff', 32, 10, 10, '库', 'zh');
    }
}
