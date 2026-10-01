<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Actions;

use BobWez98\MailLog\Contracts\UpdatesMailLog;
use BobWez98\MailLog\Data\UpdateMailLogData;
use BobWez98\MailLog\Models\MailLog;

class UpdateMailLog implements UpdatesMailLog
{
    public function update(UpdateMailLogData $data): void
    {
        MailLog::query()
            ->where('message_id', '=', $data->message_id)
            ->first()
            ?->update($data->toArray());
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesMailLog::class, static::class);
    }
}
