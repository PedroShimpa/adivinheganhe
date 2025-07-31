<?php

require __DIR__.'/vendor/autoload.php';

$paths = [
    __DIR__.'/bootstrap/app.php',
    __DIR__.'/app/Providers/AppServiceProvider.php',
    __DIR__.'/app/Http/Kernel.php',
];

foreach ($paths as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
