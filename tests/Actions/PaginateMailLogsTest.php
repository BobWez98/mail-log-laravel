<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Actions;

use BobWez98\MailLog\Actions\PaginateMailLogs;
use BobWez98\MailLog\Contracts\PaginatesMailLogs;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Models\MailLog;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

final class PaginateMailLogsTest extends TestCase
{
    #[Test]
    public function it_paginates_newest_mail_logs_first(): void
    {
        $oldest = MailLog::query()->create([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::SUCCESS,
            'from' => 'sender@example.com',
            'to' => 'oldest@example.com',
            'subject' => 'Oldest',
            'body' => '<p>Oldest</p>',
            'data' => [],
            'sent_at' => Carbon::parse('2026-10-01 10:00:00'),
            'created_at' => Carbon::parse('2026-10-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);
        $newer = MailLog::query()->create([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'newer@example.com',
            'subject' => 'Newer',
            'body' => '<p>Newer</p>',
            'data' => [],
            'sent_at' => null,
            'created_at' => Carbon::parse('2026-10-02 10:00:00'),
            'updated_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);
        $newest = MailLog::query()->create([
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'newest@example.com',
            'subject' => 'Newest',
            'body' => '<p>Newest</p>',
            'data' => [],
            'sent_at' => null,
            'created_at' => Carbon::parse('2026-10-02 10:00:00'),
            'updated_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);

        $paginator = app(PaginatesMailLogs::class);
        $firstPage = $paginator->paginate(2, 1);
        $secondPage = $paginator->paginate(2, 2);

        $this->assertInstanceOf(PaginateMailLogs::class, $paginator);
        $this->assertSame($paginator, app(PaginatesMailLogs::class));
        $this->assertSame(3, $firstPage->total());
        $this->assertSame(2, $firstPage->lastPage());
        $this->assertSame([$newest->id, $newer->id], array_column($firstPage->items(), 'id'));
        $this->assertSame([$oldest->id], array_column($secondPage->items(), 'id'));
    }
}
