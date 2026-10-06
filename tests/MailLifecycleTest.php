<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests;

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Message;
use Illuminate\Mail\SentMessage as LaravelSentMessage;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Email;

final class MailLifecycleTest extends TestCase
{
    #[Test]
    public function it_logs_the_complete_mail_lifecycle(): void
    {
        $email = new Email()
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Quarterly report')
            ->html('<p>Ready</p>');

        Str::freezeUuids(function (UuidInterface $uuid) use ($email): void {
            Event::dispatch(new MessageSending($email, ['attempt' => 1]));

            $mailLog = MailLog::query()->sole();

            $this->assertSame((string) $uuid, $mailLog->message_id);
            $this->assertSame(LogStatus::PENDING, $mailLog->status);
            $this->assertSame('sender@example.com', $mailLog->from);
            $this->assertSame('recipient@example.com', $mailLog->to);
            $this->assertSame(['attempt' => 1], $mailLog->data);
            $this->assertNull($mailLog->getRawOriginal('sent_at'));

            $sentAt = Carbon::parse('2026-10-01 12:00:00 UTC');
            $event = new MessageSent(
                new LaravelSentMessage(
                    new SymfonySentMessage($email, Envelope::create($email)),
                ),
                ['attempt' => 2],
            );

            $this->travelTo($sentAt, function () use ($event): void {
                Event::dispatch($event);
            });

            $mailLog->refresh();
            $actualSentAt = $mailLog->sent_at;

            $this->assertSame(LogStatus::SUCCESS, $mailLog->status);
            $this->assertSame(['attempt' => 2], $mailLog->data);
            $this->assertInstanceOf(Carbon::class, $actualSentAt);
            $this->assertTrue($actualSentAt->equalTo($sentAt));
            $this->assertSame('Quarterly report', $mailLog->subject);
            $this->assertSame('<p>Ready</p>', $mailLog->body);
        });
    }

    #[Test]
    public function it_delivers_and_logs_a_message_without_a_subject(): void
    {
        $mailer = Mail::build(['transport' => 'array']);
        $transport = $mailer->getSymfonyTransport();

        $this->assertInstanceOf(ArrayTransport::class, $transport);

        $sentMessage = $mailer->raw('Ready', static function (Message $message): void {
            $message
                ->from('sender@example.com')
                ->to('recipient@example.com');
        });

        $this->assertInstanceOf(LaravelSentMessage::class, $sentMessage);
        $this->assertCount(1, $transport->messages());

        $mailLog = MailLog::query()->sole();

        $this->assertSame(LogStatus::SUCCESS, $mailLog->status);
        $this->assertSame('recipient@example.com', $mailLog->to);
        $this->assertSame('', $mailLog->subject);
        $this->assertInstanceOf(Carbon::class, $mailLog->sent_at);
    }

    #[Test]
    public function it_delivers_and_logs_a_message_with_only_a_bcc_recipient(): void
    {
        $mailer = Mail::build(['transport' => 'array']);
        $transport = $mailer->getSymfonyTransport();

        $this->assertInstanceOf(ArrayTransport::class, $transport);

        $sentMessage = $mailer->raw('Ready', static function (Message $message): void {
            $message
                ->from('sender@example.com')
                ->bcc('recipient@example.com')
                ->subject('Quarterly report');
        });

        $this->assertInstanceOf(LaravelSentMessage::class, $sentMessage);
        $this->assertCount(1, $transport->messages());

        $mailLog = MailLog::query()->sole();

        $this->assertSame(LogStatus::SUCCESS, $mailLog->status);
        $this->assertSame('', $mailLog->to);
        $this->assertSame('Quarterly report', $mailLog->subject);
        $this->assertInstanceOf(Carbon::class, $mailLog->sent_at);
    }
}
