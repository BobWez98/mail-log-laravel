<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Contracts;

use BobWez98\MailLog\Models\MailLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaginatesMailLogs
{
    /** @return LengthAwarePaginator<int, MailLog> */
    public function paginate(int $perPage, ?int $page = null): LengthAwarePaginator;
}
