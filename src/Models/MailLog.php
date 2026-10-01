<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Models;

use BobWez98\MailLog\Enums\LogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $message_id
 * @property LogStatus $status
 * @property string $from
 * @property string $to
 * @property string $subject
 * @property string $body
 * @property array<string, mixed> $data
 * @property Carbon $sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MailLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => LogStatus::class,
            'data' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
