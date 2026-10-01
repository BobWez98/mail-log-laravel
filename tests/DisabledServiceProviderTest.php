<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests;

use BobWez98\MailLog\Actions\CreateMailLog;
use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Listeners\MessageSendingListener;
use BobWez98\MailLog\Listeners\MessageSentListener;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

#[DefineEnvironment('disableMailLogging')]
final class DisabledServiceProviderTest extends TestCase
{
    #[Test]
    public function it_skips_listeners_but_registers_the_other_services(): void
    {
        $this->assertFalse(config()->boolean('mail-log-laravel.enabled'));
        $this->assertInstanceOf(CreateMailLog::class, app(CreatesMailLog::class));
        $this->assertTrue(Schema::hasTable('mail_logs'));

        $events = app(Dispatcher::class);
        $listeners = $events->getRawListeners();
        $messageSendingListeners = $listeners[MessageSending::class] ?? [];
        $messageSentListeners = $listeners[MessageSent::class] ?? [];

        $this->assertIsArray($messageSendingListeners);
        $this->assertIsArray($messageSentListeners);
        $this->assertNotContains(MessageSendingListener::class, $messageSendingListeners);
        $this->assertNotContains(MessageSentListener::class, $messageSentListeners);
    }

    protected function disableMailLogging(Application $app): void
    {
        $app->make(Repository::class)->set('mail-log-laravel.enabled', false);
    }
}
