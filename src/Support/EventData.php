<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Support;

class EventData
{
    /**
     * Keep only JSON-safe values. Mailable view data may contain objects that
     * cannot be encoded or that recurse endlessly when they are serialized.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public static function sanitize(array $data): array
    {
        if (! config()->boolean('mail-log-laravel.store_data')) {
            return [];
        }

        return static::filter($data);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected static function filter(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = static::filter($value);
            } elseif (is_scalar($value) || $value === null) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
