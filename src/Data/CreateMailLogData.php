<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Data;

use BobWez98\MailLog\Enums\LogStatus;

/**
 * @property string $message_id
 * @property LogStatus $status
 * @property string $from
 * @property string $to
 * @property string $subject
 * @property string $body
 * @property array<int, mixed> $data
 *
 * @extends Data<string, mixed>
 */
class CreateMailLogData extends Data
{
    protected array $rules = [
        'message_id' => 'required',
        'status' => 'required',
        'from' => 'required',
        'to' => 'required',
        'subject' => 'required',
        'body' => 'required',
        'data' => 'required',
    ];

    /** @param array<string, mixed> $data */
    public static function new(array $data): static
    {
        return static::make($data)->validate();
    }
}
