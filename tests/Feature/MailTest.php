<?php

namespace Tests\Feature;

use App\Mail\ProfileVisitMail;
use App\Mail\SupportResponseMail;
use App\Mail\SupportMessageMail;
use App\Mail\MembershipWelcomeMail;
use App\Mail\FriendrequestMail;
use App\Mail\AcertoUsuarioMail;
use App\Mail\AcertoAdminMail;
use App\Mail\MembershipPurchaseAdminMail;
use App\Mail\MembershipReminderMail;
use App\Mail\HighRegistrationAlertMail;
use App\Mail\BanPlayerMail;
use App\Mail\NotifyAdminsOfNewTicket;
use App\Mail\NotifyNewAdivinhacaoMail;
use App\Models\Adivinhacoes;
use App\Models\User;
use App\Models\Suporte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    public function testProfileVisitMailVariables()
    {
        $user = User::factory()->create();
        $mail = new ProfileVisitMail($user->username);

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testSupportResponseMailVariables()
    {
        $user = User::factory()->create();
        $suporte = Suporte::factory()->create(['user_id' => $user->id]);
        $mail = new SupportResponseMail($suporte);

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testSupportMessageMailVariables()
    {
        $user = User::factory()->create();
        $suporte = Suporte::factory()->create(['user_id' => $user->id]);
        $mail = new SupportMessageMail($suporte, 'Test message');

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testMembershipWelcomeMailVariables()
    {
        $user = User::factory()->create();
        $mail = new MembershipWelcomeMail($user);

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testFriendrequestMailVariables()
    {
        $user = User::factory()->create();
        $mail = new FriendrequestMail('fromUser', $user->username);

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testAcertoUsuarioMailVariables()
    {
        $user = User::factory()->create();
        $mail = new AcertoUsuarioMail($user->username, Adivinhacoes::factory()->create());

        $this->assertNotEmpty($mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('unsubscribe', $mail->unsubscribeUrl);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testAcertoAdminMailVariables()
    {
        $user = User::factory()->create();
        $mail = new AcertoAdminMail($user, Adivinhacoes::factory()->create());

        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testMembershipPurchaseAdminMailVariables()
    {
        $user = User::factory()->create();
        $mail = new MembershipPurchaseAdminMail($user);

        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testMembershipReminderMailVariables()
    {
        $mail = new MembershipReminderMail();

        $this->assertEquals('#', $mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testHighRegistrationAlertMailVariables()
    {
        $mail = new HighRegistrationAlertMail();

        $this->assertEquals('#', $mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testBanPlayerMailVariables()
    {
        $mail = new BanPlayerMail();

        $this->assertEquals('#', $mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testNotifyAdminsOfNewTicketVariables()
    {
        $mail = new NotifyAdminsOfNewTicket('Nome', 'email@example.com', 'Categoria', 'Descrição');

        $this->assertEquals('#', $mail->unsubscribeUrl);
        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testNotifyNewAdivinhacaoMailVariables()
    {
        $mail = new NotifyNewAdivinhacaoMail('Título', 'http://example.com');

        $this->assertNotEmpty($mail->trackingPixel);
        $this->assertStringContainsString('img', $mail->trackingPixel);
    }

    public function testAllMailsHaveSubjectProperty()
    {
        $user = User::factory()->create();
        $suporte = Suporte::factory()->create(['user_id' => $user->id]);

        $mails = [
            new ProfileVisitMail($user->username),
            new SupportResponseMail($suporte),
            new SupportMessageMail($suporte, 'msg'),
            new MembershipWelcomeMail($user),
            new FriendrequestMail('from', $user->username),
            new AcertoUsuarioMail($user, Adivinhacoes::factory()->create()),
            new AcertoAdminMail($user, Adivinhacoes::factory()->create()),
            new MembershipPurchaseAdminMail($user),
            new MembershipReminderMail(),
            new HighRegistrationAlertMail(),
            new BanPlayerMail(),
            new NotifyAdminsOfNewTicket('n', 'e', 'c', 'd'),
            new NotifyNewAdivinhacaoMail('t', 'u'),
        ];

        foreach ($mails as $mail) {
            $this->assertIsString($mail->subject);
            $this->assertNotEmpty($mail->subject);
        }
    }

    public function testMailSending()
    {
        Mail::fake();

        $user = User::factory()->create();

        $mailInstance = new ProfileVisitMail($user->username);
        Mail::to($user->email)->send($mailInstance);

        Log::info('Sent mail', [
            'subject' => $mailInstance->subject,
            'to' => $user->email,
            'mail_class' => ProfileVisitMail::class,
        ]);

        Mail::assertSent(ProfileVisitMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
