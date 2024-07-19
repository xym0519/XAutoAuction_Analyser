<?php

namespace FindIcons;

use App\Http\FindIcons\Lib\FindIconsLib;
use TestCase;

class FindIconsTest extends TestCase
{
    public function test()
    {
        FindIconsLib::download('https://findicons.com/pack/990/vistaico_toolbar', '2');
    }
}
