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
    protected $imageFile;

    public function __construct(User $user, $imageFile)
    {
        $this->user = $user;
        $this->imageFile = $imageFile;
    }

    public function handle()
    {
        $hash = Str::random(10);
        $fileName = $hash . '_' . time() . '.webp';
        $filePath = 'usuarios/imagens/' . $fileName;

        $image = Image::read($this->imageFile)->encodeByExtension('webp', 85);

        Storage::disk('s3')->put($filePath, (string) $image);

        $urlImagem = Storage::disk('s3')->url($filePath);

        $this->user->image = $urlImagem;
        $this->user->save();
    }
}
