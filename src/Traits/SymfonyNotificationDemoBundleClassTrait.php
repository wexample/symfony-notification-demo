<?php

namespace Wexample\SymfonyNotificationDemo\Traits;

use Wexample\SymfonyHelpers\Traits\BundleClassTrait;
use Wexample\SymfonyNotificationDemo\WexampleSymfonyNotificationDemoBundle;

trait SymfonyNotificationDemoBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyNotificationDemoBundle::class;
    }
}
