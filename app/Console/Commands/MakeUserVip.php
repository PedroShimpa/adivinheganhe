<?php

namespace App\Console\Commands;

use App\Mail\MembershipPurchaseAdminMail;
use App\Mail\MembershipWelcomeMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MakeUserVip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-user-vip {user_id : The ID of the user to make VIP}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a user VIP by ID, similar to the membership controller logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        if ($user->is_vip) {
            $this->warn("User {$user->username} (ID: {$userId}) is already VIP.");
            return Command::SUCCESS;
        }

        // Upgrade user to VIP
        $user->is_vip = true;
        $user->membership_expires_at = now()->addMonth();
        $user->save();

        $this->info("User {$user->username} (ID: {$userId}) has been upgraded to VIP.");

        Log::info('User manually upgraded to VIP via command', [
            'user_id' => $userId,
            'command' => 'make-user-vip'
        ]);

        // Send WhatsApp message to community
        try {
            $API_BASE = env('NOTIFICACAO_API_BASE');
            $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
            $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
            $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

            $tokenRes = Http::post($TOKEN_ENDPOINT);

            if ($tokenRes->successful() && $tokenRes->json('status') === 'success') {
                $token = $tokenRes->json('token');
                $headers = ["Authorization" => "Bearer $token"];

                $mensagem = "🌟 Parabéns, {$user->username}!\nAgora você faz parte do grupo VIP — privilégio dos melhores! 👑";

                $payload = [
                    "phone" => $PHONE_ID,
                    "isGroup" => false,
                    "isNewsletter" => true,
                    "isLid" => false,
                    "message" => $mensagem,
                ];

                $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);

                if (!$resp->successful()) {
                    Log::error("Erro ao enviar mensagem WhatsApp para VIP: " . $resp->body());
                } else {
                    $this->info("WhatsApp notification sent for user {$user->username}.");
                }
            } else {
                Log::error("Erro ao gerar token para WhatsApp VIP: " . $tokenRes->body());
            }
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação WhatsApp para VIP: " . $e->getMessage());
        }

        // Send welcome email to user
        try {
            Mail::to($user->email)->queue((new MembershipWelcomeMail($user)));
            $this->info("Welcome email sent to {$user->email}.");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar email de boas-vindas: " . $e->getMessage());
        }

        // Notify admins
        try {
            $admins = User::where('is_admin', 'S')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user)));
            }
            $this->info("Admin notifications sent.");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificações para admins: " . $e->getMessage());
        }

        $this->info("VIP upgrade process completed for user ID {$userId}.");

        return Command::SUCCESS;
    }
}
