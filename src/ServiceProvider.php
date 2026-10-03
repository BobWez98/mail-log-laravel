<?php

declare(strict_types=1);

namespace BobWez98\MailLog;

use BobWez98\MailLog\Actions\CreateMailLog;
use BobWez98\MailLog\Actions\PaginateMailLogs;
use BobWez98\MailLog\Actions\UpdateMailLog;
use BobWez98\MailLog\Listeners\MessageSendingListener;
use BobWez98\MailLog\Listeners\MessageSentListener;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this
            ->registerConfig();
    }

    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mail-log-laravel.php', 'mail-log-laravel');

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootActions()
            ->bootConfig()
            ->bootListeners()
            ->bootMigrations();
    }

    protected function bootActions(): static
    {
        CreateMailLog::bind();
        PaginateMailLogs::bind();
        UpdateMailLog::bind();

        return $this;
    }

    protected function bootListeners(): static
    {
        if (config()->boolean('mail-log-laravel.enabled')) {
            Event::listen(
                MessageSending::class,
                MessageSendingListener::class,
            );

            Event::listen(
                MessageSent::class,
                MessageSentListener::class
            );
        }

        return $this;
    }

    protected function bootConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/mail-log-laravel.php' => config_path('mail-log-laravel.php'),
        ], 'config');

        return $this;
    }

    protected function bootMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }
}
