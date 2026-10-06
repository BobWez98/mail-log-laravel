<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Actions;

use BobWez98\MailLog\Actions\CreateMailLog;
use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Data\CreateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

final class CreateMailLogTest extends TestCase
{
    #[Test]
    public function it_persists_a_mail_log(): void
    {
        $data = CreateMailLogData::new([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
        ]);

        app(CreatesMailLog::class)->create($data);

        $mailLog = MailLog::query()->sole();

        $this->assertSame($data->message_id, $mailLog->message_id);
        $this->assertSame(LogStatus::PENDING, $mailLog->status);
        $this->assertSame($data->from, $mailLog->from);
        $this->assertSame($data->to, $mailLog->to);
        $this->assertSame($data->subject, $mailLog->subject);
        $this->assertSame($data->body, $mailLog->body);
        $this->assertSame($data->data, $mailLog->data);
        $this->assertNull($mailLog->getRawOriginal('sent_at'));
    }

    #[Test]
    public function it_persists_long_recipient_lists_and_subjects(): void
    {
        $to = str_repeat('recipient@example.com, ', 12);
        $subject = str_repeat('Quarterly report ', 16);

        $this->assertGreaterThan(255, strlen($to));
        $this->assertGreaterThan(255, strlen($subject));

        $data = CreateMailLogData::new([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => $to,
            'subject' => $subject,
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
        ]);

        app(CreatesMailLog::class)->create($data);

        $mailLog = MailLog::query()->sole();

        $this->assertSame($to, $mailLog->to);
        $this->assertSame($subject, $mailLog->subject);
    }

    #[Test]
    public function it_binds_the_contract_as_a_singleton(): void
    {
        $first = app(CreatesMailLog::class);
        $second = app(CreatesMailLog::class);

        $this->assertInstanceOf(CreateMailLog::class, $first);
        $this->assertSame($first, $second);
    }
}
