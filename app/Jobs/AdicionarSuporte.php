<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class AdicionarSuporte implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $data) {}

    public function handle(): void
    {
        DB::table('suporte')->insert($this->data);
    }
}
