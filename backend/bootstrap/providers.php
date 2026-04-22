<?php

use App\Providers\FixEncryptionServiceProvider;
use App\Providers\AppServiceProvider;

return [
    FixEncryptionServiceProvider::class,
    AppServiceProvider::class,
    // ChatServiceProvider DESATIVADO TEMPORARIAMENTE - causa erro 500 durante boot
    // ChatServiceProvider::class,
];
