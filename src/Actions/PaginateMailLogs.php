<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Actions;

use BobWez98\MailLog\Contracts\PaginatesMailLogs;
use BobWez98\MailLog\Models\MailLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginateMailLogs implements PaginatesMailLogs
{
    /** @return LengthAwarePaginator<int, MailLog> */
    public function paginate(int $perPage, ?int $page = null): LengthAwarePaginator
    {
        return MailLog::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);
    }

    public static function bind(): void
    {
        app()->singleton(PaginatesMailLogs::class, static::class);
    }
}
