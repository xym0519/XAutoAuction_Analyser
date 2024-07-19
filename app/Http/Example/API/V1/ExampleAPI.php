<?php

namespace App\Http\Example\API\V1;

use App\Http\Example\Error\ExampleErrorSet;
use App\Http\Example\Lib\ExampleLib;
use Laravel\Lumen\Routing\Controller;
use Tourmaline\UtilsIOD\Lib\I;
use Tourmaline\UtilsIOD\Lib\O;

class ExampleAPI extends Controller
{
    public function example()
    {
        $p = I::getRequired('p', ExampleErrorSet::$exampleError);
        return O::get(ExampleLib::example($p));
    }
}
