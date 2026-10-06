<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Listeners;

use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Data\CreateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Listeners\MessageSendingListener;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;

final class MessageSendingListenerTest extends TestCase
{
    #[Test]
    public function it_adds_a_tracking_header_and_delegates_creation(): void
    {
        $eventData = ['attempt' => 1];
        $email = new Email()
            ->from('Sender <sender@example.com>')
            ->to('recipient@example.com', 'other@example.com')
            ->subject('Quarterly report')
            ->html('<p>Ready</p>');
        $event = new MessageSending($email, $eventData);

        Str::freezeUuids(function (UuidInterface $uuid) use ($event, $eventData, $email): void {
            $this->mock(CreatesMailLog::class, function (MockInterface $mock) use ($uuid, $eventData): void {
                $mock
                    ->shouldReceive('create')
                    ->once()
                    ->withArgs(function (CreateMailLogData $data) use ($uuid, $eventData): bool {
                        $this->assertSame((string) $uuid, $data->message_id);
                        $this->assertSame(LogStatus::PENDING, $data->status);
                        $this->assertSame('"Sender" <sender@example.com>', $data->from);
                        $this->assertSame('recipient@example.com, other@example.com', $data->to);
                        $this->assertSame('Quarterly report', $data->subject);
                        $this->assertSame('<p>Ready</p>', $data->body);
                        $this->assertSame($eventData, $data->data);

                        return true;
                    });
            });

            app(MessageSendingListener::class)->handle($event);

            $header = $email->getHeaders()->get('X-Mail-Log-ID');

            $this->assertInstanceOf(HeaderInterface::class, $header);
            $this->assertSame((string) $uuid, $header->getBodyAsString());
        });
    }

    #[Test]
    public function it_uses_the_plain_text_body_when_html_is_missing(): void
    {
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->text('Ready');
        $event = new MessageSending($email, ['attempt' => 1]);

        $this->mock(CreatesMailLog::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->withArgs(function (CreateMailLogData $data): bool {
                    $this->assertSame('Ready', $data->body);

                    return true;
                });
        });

        app(MessageSendingListener::class)->handle($event);
    }

    #[Test]
    public function it_swallows_validation_failures_without_creating_a_log(): void
    {
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report');
        $event = new MessageSending($email, ['attempt' => 1]);

        Str::freezeUuids(function () use ($event, $email): void {
            $this->mock(CreatesMailLog::class, function (MockInterface $mock): void {
                $mock->shouldNotReceive('create');
            });

            app(MessageSendingListener::class)->handle($event);

            $this->assertTrue($email->getHeaders()->has('X-Mail-Log-ID'));
        });
    }

    #[Test]
    public function it_reports_creation_failures_without_throwing(): void
    {
        $exception = new RuntimeException('Unable to create mail log.');
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->text('Ready');
        $event = new MessageSending($email, ['attempt' => 1]);

        Exceptions::fake();

        $this->mock(CreatesMailLog::class, function (MockInterface $mock) use ($exception): void {
            $mock
                ->shouldReceive('create')
                ->once()
                ->andThrow($exception);
        });

        app(MessageSendingListener::class)->handle($event);

        Exceptions::assertReported(
            fn (RuntimeException $reported): bool => $reported === $exception,
        );
        Exceptions::assertReportedCount(1);
        $this->assertTrue($email->getHeaders()->has('X-Mail-Log-ID'));
    }
}
