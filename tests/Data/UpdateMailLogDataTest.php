<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Data;

use BobWez98\MailLog\Data\UpdateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class UpdateMailLogDataTest extends TestCase
{
    #[Test]
    public function it_creates_validated_data(): void
    {
        $attributes = self::validAttributes();

        $data = UpdateMailLogData::new($attributes);

        $this->assertSame($attributes, $data->toArray());
    }

    #[Test]
    #[DataProvider('requiredFields')]
    public function it_requires_each_configured_field(string $field): void
    {
        $attributes = self::validAttributes();
        unset($attributes[$field]);

        try {
            UpdateMailLogData::new($attributes);
            $this->fail('Validation was expected to fail.');
        } catch (ValidationException $validationException) {
            $this->assertArrayHasKey($field, $validationException->errors());
        }
    }

    #[Test]
    public function it_rejects_empty_event_data(): void
    {
        $attributes = self::validAttributes();
        $attributes['data'] = [];

        try {
            UpdateMailLogData::new($attributes);
            $this->fail('Validation was expected to fail.');
        } catch (ValidationException $validationException) {
            $this->assertArrayHasKey('data', $validationException->errors());
        }
    }

    /** @return \Iterator<string, array<int, string>> */
    public static function requiredFields(): \Iterator
    {
        yield 'message_id' => ['message_id'];
        yield 'status' => ['status'];
        yield 'data' => ['data'];
        yield 'sent_at' => ['sent_at'];
    }

    /** @return array<string, mixed> */
    protected static function validAttributes(): array
    {
        return [
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::SUCCESS,
            'data' => ['attempt' => 1],
            'sent_at' => Carbon::parse('2026-10-01 12:00:00 UTC'),
        ];
    }
}
