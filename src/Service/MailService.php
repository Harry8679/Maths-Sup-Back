<?php

namespace App\Service;

use App\Entity\User;
use Mailjet\Client as MailjetClient;
use Mailjet\Resources;

class MailService
{
    private MailjetClient $client;
    private string $frontendUrl;

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->client = new MailjetClient($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    }

    public function sendVerificationEmail(User $user): void
    {
        $email = $user->getEmail();
        $token = $user->getEmailVerificationToken();
        // $url = 'https://tondomaine.com/verify-email?token=' . $token;
        $url = $this->frontendUrl . '/verify-email?token=' . $token;

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ton@email.com",
                        'Name' => "Annuaire Prépa Gabon"
                    ],
                    'To' => [
                        ['Email' => $email]
                    ],
                    'Subject' => "Confirme ton adresse email",
                    'TextPart' => "Clique sur ce lien pour confirmer ton email : $url"
                ]
            ]
        ];

        $this->client->post(Resources::$Email, ['body' => $body]);
    }
}