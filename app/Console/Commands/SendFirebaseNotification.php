<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestFirebaseNotification;
use Illuminate\Console\Command;

class SendFirebaseNotification extends Command
{
    protected $signature = 'firebase:send-notification {title} {body} {--data=*}';
    protected $description = 'Send a Firebase push notification to all users with push tokens';

    public function handle()
    {
        $title = $this->argument('title');
        $body = $this->argument('body');
        $data = $this->option('data');

        $users = User::whereNotNull('token_push_notification')->get();

        if ($users->isEmpty()) {
            $this->info('No users with push tokens found.');
            return Command::SUCCESS;
        }

        $this->info("Sending notification to {$users->count()} users...");

        foreach ($users as $user) {
            $user->notify(new TestFirebaseNotification($title, $body, $data));
        }

        $this->info('Notifications sent successfully.');
        return Command::SUCCESS;
    }
}
