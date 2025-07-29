// /var/www/adivinheganhe/preload.php

<?php

// Preload Composer autoload (classes essenciais)
require __DIR__.'/vendor/autoload.php';

// Preload arquivos principais do Laravel
$paths = [
    __DIR__.'/bootstrap/app.php',
    __DIR__.'/app/Providers/AppServiceProvider.php',
    __DIR__.'/app/Http/Kernel.php',
    __DIR__.'/routes/web.php',
];

foreach ($paths as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
