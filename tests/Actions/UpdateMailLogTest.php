<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Actions;

use BobWez98\MailLog\Actions\UpdateMailLog;
use BobWez98\MailLog\Contracts\UpdatesMailLog;
use BobWez98\MailLog\Data\UpdateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

final class UpdateMailLogTest extends TestCase
{
    #[Test]
    public function it_updates_the_matching_mail_log(): void
    {
        $mailLog = MailLog::query()->create([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
        ]);

        $sentAt = Carbon::parse('2026-10-01 12:00:00 UTC');

        app(UpdatesMailLog::class)->update(UpdateMailLogData::new([
            'message_id' => $mailLog->message_id,
            'status' => LogStatus::SUCCESS,
            'data' => ['attempt' => 2],
            'sent_at' => $sentAt,
        ]));

        $mailLog->refresh();
        $actualSentAt = $mailLog->sent_at;

        $this->assertSame(LogStatus::SUCCESS, $mailLog->status);
        $this->assertSame(['attempt' => 2], $mailLog->data);
        $this->assertInstanceOf(Carbon::class, $actualSentAt);
        $this->assertTrue($actualSentAt->equalTo($sentAt));
        $this->assertSame('sender@example.com', $mailLog->from);
        $this->assertSame('recipient@example.com', $mailLog->to);
        $this->assertSame('Quarterly report', $mailLog->subject);
        $this->assertSame('<p>Ready</p>', $mailLog->body);
    }

    #[Test]
    public function it_ignores_an_unknown_message_id(): void
    {
        $messageId = (string) Str::uuid();

        MailLog::query()->create([
            'message_id' => $messageId,
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
        ]);

        app(UpdatesMailLog::class)->update(UpdateMailLogData::new([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::SUCCESS,
            'data' => ['attempt' => 2],
            'sent_at' => Carbon::parse('2026-10-01 12:00:00 UTC'),
        ]));

        $mailLog = MailLog::query()->sole();

        $this->assertSame(LogStatus::PENDING, $mailLog->status);
        $this->assertSame(['attempt' => 1], $mailLog->data);
    }

    #[Test]
    public function it_binds_the_contract_as_a_singleton(): void
    {
        $first = app(UpdatesMailLog::class);
        $second = app(UpdatesMailLog::class);

        $this->assertInstanceOf(UpdateMailLog::class, $first);
        $this->assertSame($first, $second);
    }
}
