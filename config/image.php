<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. Depending on your PHP setup, you can choose one of them.
    |
    | Included options:
    |   - \Intervention\Image\Drivers\Gd\Driver::class
    |   - \Intervention\Image\Drivers\Imagick\Driver::class
    |
    | Note: To use GD driver, ensure the GD extension is enabled in php.ini
    | by uncommenting: extension=gd
    |
    | To use Imagick driver, ensure the Imagick extension is installed and
    | enabled in php.ini by uncommenting: extension=imagick
    |
    */

    'driver' => env('IMAGE_DRIVER', \Intervention\Image\Drivers\Gd\Driver::class),

];
