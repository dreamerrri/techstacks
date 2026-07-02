<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class MailtrapApiTransport extends AbstractTransport
{
    public function __construct(
        protected string $apiToken,
        protected string $host,
        protected ?string $inboxId = null,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'from'    => $this->formatAddress($email->getFrom()[0]),
            'to'      => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = $this->formatAddress($replyTo[0]);
        }

        if ($text = $email->getTextBody()) {
            $payload['text'] = $text;
        }

        if ($html = $email->getHtmlBody()) {
            $payload['html'] = $html;
        }

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename') ?? 'attachment';

            $payload['attachments'][] = [
                'content'     => base64_encode($attachment->getBody()),
                'filename'    => $filename,
                'type'        => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
                'disposition' => $headers->getHeaderBody('Content-Disposition') === 'inline' ? 'inline' : 'attachment',
            ];
        }

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->post($this->endpoint(), $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                "Mailtrap API request failed [{$response->status()}]: {$response->body()}"
            );
        }
    }

    /**
     * Sandbox host requires the inbox id in the path.
     * Production (send/bulk) host does not.
     */
    protected function endpoint(): string
    {
        if (str_contains($this->host, 'sandbox')) {
            if (! $this->inboxId) {
                throw new RuntimeException(
                    'MAILTRAP_INBOX_ID must be set when using the Mailtrap sandbox API host.'
                );
            }

            return "https://{$this->host}/api/send/{$this->inboxId}";
        }

        return "https://{$this->host}/api/send";
    }

    protected function formatAddress(Address $address): array
    {
        $formatted = ['email' => $address->getAddress()];

        if ($name = $address->getName()) {
            $formatted['name'] = $name;
        }

        return $formatted;
    }

    protected function formatAddresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->formatAddress($address), $addresses);
    }

    public function __toString(): string
    {
        return 'mailtrap-api://' . $this->host;
    }
}