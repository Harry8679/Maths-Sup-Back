<?php

namespace App\Service;

use App\Entity\User;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;

class MailService
{
    private MailjetClient $client;
    private string $fromEmail = "emarh.harry.code@gmail.com";
    private string $fromName = "Annuaire Prépas Gabon";

    public function __construct(string $mailjetApiKey, string $mailjetApiSecret)
    {
        $this->client = new MailjetClient($mailjetApiKey, $mailjetApiSecret, true, ['version' => 'v3.1']);
    }

    public function sendVerificationEmail(User $user): void
    {
        
    }

}