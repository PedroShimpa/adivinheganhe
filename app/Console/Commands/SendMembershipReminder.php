<?php

namespace App\Console\Commands;

use App\Mail\MembershipReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendMembershipReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-membership-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send membership reminder emails to users who visited the membership page but didn\'t purchase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find logs where message = 'Membership page accessed'
        $logs = DB::table('logs')
            ->where('message', 'Membership page accessed')
            ->where('created_at', '>=', now()->subDays(1)) // Only recent logs (last 24 hours)
            ->get();

        $this->info("Found {$logs->count()} membership page access logs.");

        // Group by user_id to send only one email per user
        $userIds = [];
        foreach ($logs as $log) {
            $context = json_decode($log->context, true);
            if ($context && isset($context['user_id'])) {
                $userIds[] = $context['user_id'];
            }
        }
        $uniqueUserIds = array_unique($userIds);

        $this->info("Found " . count($uniqueUserIds) . " unique users to potentially send reminders to.");

        $sentCount = 0;

        foreach ($uniqueUserIds as $userId) {
            $user = User::find($userId);

            if (!$user || $user->is_vip == 1 || !$user->email_verified_at) {
                // Skip if user not found, is VIP, or email not verified
                // Delete all logs for this user
                DB::table('logs')
                    ->where('message', 'Membership page accessed')
                    ->whereRaw("JSON_EXTRACT(context, '$.user_id') = ?", [$userId])
                    ->delete();
                continue;
            }

            // Send email
            Mail::to($user->email)->queue((new MembershipReminderMail())->track($user->email, 'Lembrete: Vamos finalizar a compra do seu VIP?'));
            $this->info("Sent reminder to: {$user->email}");

            // Remove all logs for this user after sending
            DB::table('logs')
                ->where('message', 'Membership page accessed')
                ->whereRaw("JSON_EXTRACT(context, '$.user_id') = ?", [$userId])
                ->delete();

            $sentCount++;
        }

        $this->info("Membership reminder emails sent to {$sentCount} users successfully.");
    }
}
