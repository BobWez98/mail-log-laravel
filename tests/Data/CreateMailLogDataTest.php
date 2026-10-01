<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Data;

use BobWez98\MailLog\Data\CreateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class CreateMailLogDataTest extends TestCase
{
    #[Test]
    public function it_creates_validated_data(): void
    {
        $attributes = self::validAttributes();

        $data = CreateMailLogData::new($attributes);

        $this->assertSame($attributes, $data->toArray());
        $this->assertSame($data, $data->validate());
    }

    #[Test]
    #[DataProvider('requiredFields')]
    public function it_requires_each_configured_field(string $field): void
    {
        $attributes = self::validAttributes();
        unset($attributes[$field]);

        try {
            CreateMailLogData::new($attributes);
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
            CreateMailLogData::new($attributes);
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
        yield 'from' => ['from'];
        yield 'to' => ['to'];
        yield 'subject' => ['subject'];
        yield 'body' => ['body'];
        yield 'data' => ['data'];
    }

    /** @return array<string, mixed> */
    protected static function validAttributes(): array
    {
        return [
            'message_id' => (string) Str::uuid(),
            'status' => LogStatus::PENDING,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'subject' => 'Quarterly report',
            'body' => '<p>Ready</p>',
            'data' => ['attempt' => 1],
        ];
    }
}
