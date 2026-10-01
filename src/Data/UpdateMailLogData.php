<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Data;

use BobWez98\MailLog\Enums\LogStatus;
use Illuminate\Support\Carbon;

/**
 * @property string $message_id
 * @property LogStatus $status
 * @property array<int, mixed> $data
 * @property Carbon $sent_at
 *
 * @extends Data<string, mixed>
 */
class UpdateMailLogData extends Data
{
    protected array $rules = [
        'message_id' => 'required',
        'status' => 'required',
        'data' => 'required',
        'sent_at' => 'required',
    ];

    /** @param array<string, mixed> $data */
    public static function new(array $data): static
    {
        return static::make($data)->validate();
    }
}
