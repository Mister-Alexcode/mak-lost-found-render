<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's transactional email HTTP API.
 *
 * Render blocks outbound SMTP ports, so we talk to Brevo over HTTPS (443)
 * instead of SMTP. Brevo lets you verify a single sender address (no domain
 * required) and then send to any recipient, which matches the old Gmail flow.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? null;
        if (! $from) {
            throw new \RuntimeException('Brevo send failed: no "from" address set (check MAIL_FROM_ADDRESS).');
        }

        $payload = [
            'sender'  => $this->toAddress($from),
            'to'      => array_map([$this, 'toAddress'], $email->getTo()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map([$this, 'toAddress'], $cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map([$this, 'toAddress'], $bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = $this->toAddress($replyTo[0]);
        }
        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = $html;
        }
        if ($text = $email->getTextBody()) {
            $payload['textContent'] = $text;
        }
        // Brevo requires htmlContent or textContent to be present.
        if (empty($payload['htmlContent']) && empty($payload['textContent'])) {
            $payload['textContent'] = ' ';
        }

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename') ?: 'attachment';
            $payload['attachment'][] = [
                'name'    => $filename,
                'content' => base64_encode($attachment->getBody()),
            ];
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept'  => 'application/json',
        ])->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Brevo API send failed ('.$response->status().'): '.$response->body()
            );
        }
    }

    private function toAddress(Address $address): array
    {
        $data = ['email' => $address->getAddress()];
        if ($name = $address->getName()) {
            $data['name'] = $name;
        }

        return $data;
    }

    public function __toString(): string
    {
        return 'brevo+api://default';
    }
}
