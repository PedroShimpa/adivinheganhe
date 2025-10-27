<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\Pagamentos;
use App\Models\User;
use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\DicasCompras;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use App\Mail\MembershipWelcomeMail;
use App\Mail\MembershipPurchaseAdminMail;

class VerifyMercadoPagoPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:verify-mercado-pago {--limit=50 : Number of payments to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify pending MercadoPago payments and process approved ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');

        $this->info("Starting MercadoPago payment verification for {$limit} pending payments...");

        // Get pending payments that haven't been processed yet
        $pendingPayments = Pagamentos::where('payment_status', 'pending')
            ->where('processed', false)
            ->whereNotNull('payment_id')
            ->limit($limit)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->info('No pending payments found to verify.');
            return;
        }

        $this->info("Found {$pendingPayments->count()} pending payments to check.");

        $processed = 0;
        $approved = 0;
        $failed = 0;

        MercadoPagoConfig::setAccessToken(env("MERCADO_PAGO_ACCESS_TOKEN"));
        $client = new PaymentClient();

        $progressBar = $this->output->createProgressBar($pendingPayments->count());
        $progressBar->start();

        foreach ($pendingPayments as $pagamento) {
            try {
                $payment = $client->get($pagamento->payment_id);

                if ($payment->status === 'approved') {
                    $this->processApprovedPayment($pagamento, $payment);
                    $approved++;
                    $processed++;
                } elseif (in_array($payment->status, ['cancelled', 'rejected', 'refunded'])) {
                    // Mark as processed even if failed to avoid rechecking
                    $pagamento->processed = true;
                    $pagamento->payment_status = $payment->status;
                    $pagamento->save();
                    $processed++;
                }

                // Update payment status
                $pagamento->payment_status = $payment->status;
                $pagamento->save();

            } catch (\Exception $e) {
                $this->error("Error checking payment {$pagamento->payment_id}: " . $e->getMessage());
                Log::error('Error verifying MercadoPago payment', [
                    'payment_id' => $pagamento->payment_id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Verification completed:");
        $this->info("- Total checked: {$pendingPayments->count()}");
        $this->info("- Approved and processed: {$approved}");
        $alreadyProcessed = $processed - $approved;
        $this->info("- Already processed: {$alreadyProcessed}");
        $this->info("- Failed to check: {$failed}");

        return Command::SUCCESS;
    }

    private function processApprovedPayment($pagamento, $payment)
    {
        $user = $pagamento->user;

        if (!$user) {
            Log::error('User not found for payment processing', [
                'payment_id' => $pagamento->payment_id,
                'user_id' => $pagamento->user_id
            ]);
            return;
        }

        // Check payment type and process accordingly
        if (str_contains($pagamento->desc, 'VIP mensal')) {
            // Process VIP membership
            $user->is_vip = true;
            $user->membership_expires_at = now()->addMonth();
            $user->save();

            Log::info('User upgraded to VIP via command verification', [
                'user_id' => $user->id,
                'payment_id' => $pagamento->payment_id
            ]);

            // Send WhatsApp message to community
            $this->sendWhatsAppNotification($user);

            // Send welcome email to user
            Mail::to($user->email)->queue((new MembershipWelcomeMail($user)));

            // Notify admins
            $admins = User::where('is_admin', 'S')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user)));
            }

        } elseif (str_contains($pagamento->desc, 'palpites')) {
            // Process attempts purchase
            $quantidade = (int) filter_var($pagamento->desc, FILTER_SANITIZE_NUMBER_INT);
            $indicated = AdicionaisIndicacao::where('user_uuid', $user->uuid)->first();
            if (!empty($indicated)) {
                $indicated->value = $indicated->value + $quantidade;
                $indicated->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => $user->uuid, 'value' => $quantidade]);
            }
            $uuid = $user->uuid;
            Cache::delete("indicacoes_{$uuid}");

        } elseif (str_contains($pagamento->desc, 'dica')) {
            // Process dica purchase - extract adivinhacao_id from description
            preg_match('/Compra de dica - (.+)/', $pagamento->desc, $matches);
            if (isset($matches[1])) {
                $titulo = $matches[1];
                $adivinhacao = Adivinhacoes::where('titulo', $titulo)->first();
                if ($adivinhacao) {
                    DicasCompras::create([
                        'user_id' => $user->id,
                        'adivinhacao_id' => $adivinhacao->id,
                        'pagamento_id' => $pagamento->id
                    ]);
                }
            }
        }

        $pagamento->processed = true;
        $pagamento->save();

        Log::info('Payment processed successfully via command', [
            'user_id' => $user->id,
            'payment_id' => $pagamento->payment_id,
            'type' => $this->getPaymentType($pagamento->desc)
        ]);
    }

    private function sendWhatsAppNotification($user)
    {
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
                }
            } else {
                Log::error("Erro ao gerar token para WhatsApp VIP: " . $tokenRes->body());
            }
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação WhatsApp para VIP: " . $e->getMessage());
        }
    }

    private function getPaymentType($description)
    {
        if (str_contains($description, 'VIP mensal')) {
            return 'vip';
        } elseif (str_contains($description, 'palpites')) {
            return 'attempts';
        } elseif (str_contains($description, 'dica')) {
            return 'dica';
        }
        return 'unknown';
    }
}
