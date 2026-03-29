<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class InfobipClient
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(rtrim(config('services.infobip.base_url'), '/'))
            ->withHeaders([
                'Authorization' => 'App ' . config('services.infobip.api_key'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(15)
            ->retry(3, 500, fn (\Throwable $e) => $e instanceof ConnectionException);
    }

    /**
     * Send a text SMS message.
     *
     * @param  string  $to      E.164 phone number, e.g. +263771234567
     * @param  string  $text    Plain-text message body
     * @return array            Infobip response
     */
    public function sendSms(string $to, string $text): array
    {
        return $this->http
            ->post('/sms/2/text/advanced', [
                'messages' => [[
                    'from'         => config('services.infobip.sms_sender'),
                    'destinations' => [['to' => $to]],
                    'text'         => $text,
                ]],
            ])
            ->throw()
            ->json();
    }

    /**
     * Send a WhatsApp template message.
     *
     * @param  string  $to            E.164 phone number
     * @param  string  $templateName  Approved Meta/Infobip template name
     * @param  array   $placeholders  Body placeholder values (positional)
     * @return array
     */
    public function sendWhatsAppTemplate(string $to, string $templateName, array $placeholders = []): array
    {
        return $this->http
            ->post('/whatsapp/1/message/template', [
                'from'    => config('services.infobip.whatsapp_sender'),
                'to'      => $to,
                'content' => [
                    'templateName' => $templateName,
                    'templateData' => [
                        'body' => ['placeholders' => $placeholders],
                    ],
                    'language' => 'en',
                ],
            ])
            ->throw()
            ->json();
    }

    /**
     * Send a free-form WhatsApp text message (only valid during an active session/window).
     */
    public function sendWhatsAppText(string $to, string $text): array
    {
        return $this->http
            ->post('/whatsapp/1/message/text', [
                'from'    => config('services.infobip.whatsapp_sender'),
                'to'      => $to,
                'content' => ['text' => $text],
            ])
            ->throw()
            ->json();
    }
    /**
     * Send an email message via Infobip Email API v3 (multipart/form-data).
     * Uses a separate HTTP client instance to avoid the application/json Content-Type
     * header set on the shared $this->http client.
     */
    public function sendEmail(string $to, string $subject, string $htmlBody): array
    {
        return Http::baseUrl(rtrim(config('services.infobip.base_url'), '/'))
            ->withHeaders([
                'Authorization' => 'App ' . config('services.infobip.api_key'),
                'Accept'        => 'application/json',
            ])
            ->timeout(15)
            ->retry(3, 500, fn (\Throwable $e) => $e instanceof ConnectionException)
            ->asMultipart()
            ->post('/email/3/send', [
                ['name' => 'from',    'contents' => config('services.infobip.email_sender', config('mail.from.address'))],
                ['name' => 'to',      'contents' => $to],
                ['name' => 'subject', 'contents' => $subject],
                ['name' => 'html',    'contents' => $htmlBody],
            ])
            ->throw()
            ->json();
    }
}
