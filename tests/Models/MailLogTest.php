<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Models;

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

final class MailLogTest extends TestCase
{
    #[Test]
    public function it_is_unguarded_and_casts_its_attributes(): void
    {
        $sentAt = Carbon::parse('2026-10-01 12:00:00 UTC');
        $mailLog = MailLog::query()->create([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::SUCCESS,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
            'sent_at' => $sentAt,
        ]);

        $mailLog->refresh();

        $this->assertSame([], $mailLog->getGuarded());
        $this->assertSame(LogStatus::class, $mailLog->getCasts()['status']);
        $this->assertSame('array', $mailLog->getCasts()['data']);
        $this->assertSame('datetime', $mailLog->getCasts()['sent_at']);
        $this->assertSame(LogStatus::SUCCESS, $mailLog->status);
        $this->assertSame('sender@example.com', $mailLog->from);
        $this->assertSame('recipient@example.com', $mailLog->to);
        $this->assertSame(['attempt' => 1], $mailLog->data);
        $this->assertInstanceOf(Carbon::class, $mailLog->sent_at);
        $this->assertTrue($mailLog->sent_at->equalTo($sentAt));
    }
}
