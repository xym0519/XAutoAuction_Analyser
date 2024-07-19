<?php

namespace App\Config;

use Tourmaline\Infrastructure\Config\TourmalineConfig;

class Config extends TourmalineConfig
{
    public const AppID = 0;
    public const VersionIDAdmin = 0;

    public $appConfig = [
        "serviceVersion" => "2.0.0",
        "serviceIdentifier" => "Inception Service Based On Tourmaline Framework",
        "crossDomainAllowOrigin" => "*",
        "crossDomainAllowCredentials" => "true",
        "crossDomainAllowHeaders" => "CBS-User-Id,CBS-Authorization,CBS-Application-Id,CBS-Version-Id",
        "crossDomainExposeHeaders" => "CBS-Service-Id,CBS-Service-Version,count",
        "crossDomainAllowMethods" => "PUT,GET,POST,DELETE,PATCH"
    ];
}