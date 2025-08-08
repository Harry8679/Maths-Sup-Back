<?php

namespace App\Service;

use App\Entity\User;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;

class MailService
{
    private MailjetClient $client;
    private string $frontendUrl;
    private string $fromEmail = "emarh.harry.code@gmail.com";
    private string $fromName = "Annuaire Prépas Gabon";

    public function __construct(string $mailjetApiKey, string $mailjetApiSecret, string $frontendUrl)
    {
        $this->client = new MailjetClient($mailjetApiKey, $mailjetApiSecret, true, ['version' => 'v3.1']);
        $this->frontendUrl = rtrim($frontendUrl, '/');
    }

    public function sendVerificationEmail(User $user): void
    {
        $email = $user->getEmail();
        $token = $user->getEmailVerificationToken();
        $url = $this->frontendUrl . '/verify-email?token=' . $token;
    }

}