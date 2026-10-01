<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Actions;

use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Data\CreateMailLogData;
use BobWez98\MailLog\Models\MailLog;

class CreateMailLog implements CreatesMailLog
{
    public function create(CreateMailLogData $data): void
    {
        MailLog::create($data->toArray());
    }

    public static function bind(): void
    {
        app()->singleton(CreatesMailLog::class, static::class);
    }
}
