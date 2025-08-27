<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\User;

class UploadUserImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $filePath;

    public function __construct(User $user, $filePath)
    {
        $this->user = $user;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $hash = Str::random(10);
        $fileName = $hash . '_' . time() . '.webp';
        $s3Path = 'usuarios/imagens/' . $fileName;

        $image = Image::read($this->filePath)->encodeByExtension('webp', 85);

        Storage::disk('s3')->put($s3Path, (string) $image);

        $urlImagem = Storage::disk('s3')->url($s3Path);

        $this->user->image = $urlImagem;
        $this->user->save();

        // Opcional: apagar o arquivo temporário
        @unlink($this->filePath);
    }
}
