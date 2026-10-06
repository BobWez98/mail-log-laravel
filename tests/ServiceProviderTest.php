<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests;

use BobWez98\MailLog\Actions\CreateMailLog;
use BobWez98\MailLog\Actions\UpdateMailLog;
use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Contracts\UpdatesMailLog;
use BobWez98\MailLog\Listeners\MessageSendingListener;
use BobWez98\MailLog\Listeners\MessageSentListener;
use BobWez98\MailLog\ServiceProvider;
use Illuminate\Events\Dispatcher;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_package_services(): void
    {
        $this->assertTrue(config()->boolean('mail-log-laravel.enabled'));
        $this->assertInstanceOf(CreateMailLog::class, app(CreatesMailLog::class));
        $this->assertInstanceOf(UpdateMailLog::class, app(UpdatesMailLog::class));
        $this->assertTrue(Schema::hasTable('mail_logs'));
        $this->assertContains(config_path('mail-log-laravel.php'), array_values(ServiceProvider::pathsToPublish(ServiceProvider::class, 'config')));

        $events = app(Dispatcher::class);
        $listeners = $events->getRawListeners();
        $messageSendingListeners = $listeners[MessageSending::class] ?? null;
        $messageSentListeners = $listeners[MessageSent::class] ?? null;

        $this->assertIsArray($messageSendingListeners);
        $this->assertIsArray($messageSentListeners);
        $this->assertContains(MessageSendingListener::class, $messageSendingListeners);
        $this->assertContains(MessageSentListener::class, $messageSentListeners);
    }

    #[Test]
    public function it_uses_text_columns_for_recipients_and_subjects(): void
    {
        $this->assertSame('text', Schema::getColumnType('mail_logs', 'to'));
        $this->assertSame('text', Schema::getColumnType('mail_logs', 'subject'));
    }
}
