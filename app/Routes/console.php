<?php
# app/routes/console.php
# php artisan send-mail

use Illuminate\Support\Facades\Artisan;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

Artisan::command('send-mail', function () {
    $email = (new MailtrapEmail())
        ->from(new Address('hello@example.com', 'Mailtrap Test'))
        ->to(new Address('andrewrennn@gmail.com'))
        ->subject('You are awesome!')
        ->category('Integration Test')
        ->text('Congrats for sending test email with Mailtrap!')
    ;

    $response = MailtrapClient::initSendingEmails(
        apiKey: config('mail.mailers.mailtrap-api.api_token'),
        isSandbox: true,
        inboxId: env('MAILTRAP_INBOX_ID')
    )->send($email);

    var_dump(ResponseHelper::toArray($response));
})->purpose('Send Mail');