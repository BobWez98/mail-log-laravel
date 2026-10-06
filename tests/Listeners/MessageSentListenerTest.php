<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Listeners;

use BobWez98\MailLog\Contracts\UpdatesMailLog;
use BobWez98\MailLog\Data\UpdateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Listeners\MessageSentListener;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage as LaravelSentMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Email;

final class MessageSentListenerTest extends TestCase
{
    #[Test]
    public function it_delegates_a_successful_update(): void
    {
        $messageId = (string) Str::uuid();
        $eventData = ['attempt' => 2];
        $sentAt = Carbon::parse('2026-10-01 12:00:00 UTC');
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->html('<p>Ready</p>');
        $email->getHeaders()->addTextHeader('X-Mail-Log-ID', $messageId);
        $event = self::messageSentEvent($email, $eventData);

        $this->travelTo($sentAt, function () use ($messageId, $eventData, $sentAt, $event): void {
            $this->mock(UpdatesMailLog::class, function (MockInterface $mock) use ($messageId, $eventData, $sentAt): void {
                $mock
                    ->shouldReceive('update')
                    ->once()
                    ->withArgs(function (UpdateMailLogData $data) use ($messageId, $eventData, $sentAt): bool {
                        $this->assertSame($messageId, $data->message_id);
                        $this->assertSame($eventData, $data->data);
                        $this->assertSame(LogStatus::SUCCESS, $data->status);
                        $this->assertTrue($data->sent_at->equalTo($sentAt));

                        return true;
                    });
            });

            app(MessageSentListener::class)->handle($event);
        });
    }

    #[Test]
    public function it_does_not_update_without_a_tracking_header(): void
    {
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->html('<p>Ready</p>');
        $event = self::messageSentEvent($email, []);

        $this->mock(UpdatesMailLog::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('update');
        });

        app(MessageSentListener::class)->handle($event);

        $this->assertFalse($email->getHeaders()->has('X-Mail-Log-ID'));
    }

    #[Test]
    public function it_reports_update_failures_without_throwing(): void
    {
        $exception = new RuntimeException('Unable to update mail log.');
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->html('<p>Ready</p>');
        $email->getHeaders()->addTextHeader('X-Mail-Log-ID', (string) Str::uuid());
        $event = self::messageSentEvent($email, ['attempt' => 2]);

        Exceptions::fake();

        $this->mock(UpdatesMailLog::class, function (MockInterface $mock) use ($exception): void {
            $mock
                ->shouldReceive('update')
                ->once()
                ->andThrow($exception);
        });

        app(MessageSentListener::class)->handle($event);

        Exceptions::assertReported(
            fn (RuntimeException $reported): bool => $reported === $exception,
        );
        Exceptions::assertReportedCount(1);
    }

    /** @param array<string, mixed> $data */
    protected static function messageSentEvent(Email $email, array $data): MessageSent
    {
        return new MessageSent(
            new LaravelSentMessage(
                new SymfonySentMessage($email, Envelope::create($email)),
            ),
            $data,
        );
    }
}
