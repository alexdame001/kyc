<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Mailer;

class CustomMailServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Mail::extend('custom_smtp', function ($app, $config) {
            $transport = new EsmtpTransport(
                $config['host'],
                $config['port'],
                $config['encryption'] === 'ssl'
            );

            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);

            $transport->setStreamOptions([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            return new Mailer($transport);
        });
    }
}
