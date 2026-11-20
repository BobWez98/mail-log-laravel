<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Support;

use BobWez98\MailLog\Support\EventData;
use BobWez98\MailLog\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

final class EventDataTest extends TestCase
{
    #[Test]
    public function it_keeps_scalars_null_and_nested_arrays(): void
    {
        $data = [
            'attempt' => 1,
            'ratio' => 1.5,
            'queued' => false,
            'subject' => 'Quarterly report',
            'delay' => null,
            'recipient' => ['name' => 'Recipient', 'tags' => ['a', 'b']],
        ];

        $this->assertSame($data, EventData::sanitize($data));
    }

    #[Test]
    public function it_removes_objects_at_every_depth(): void
    {
        $data = [
            'attempt' => 1,
            'message' => new stdClass,
            'user' => [
                'name' => 'Recipient',
                'model' => new stdClass,
            ],
        ];

        $this->assertSame([
            'attempt' => 1,
            'user' => ['name' => 'Recipient'],
        ], EventData::sanitize($data));
    }

    #[Test]
    public function it_stores_nothing_when_disabled(): void
    {
        config()->set('mail-log-laravel.store_data', false);

        $this->assertSame([], EventData::sanitize(['attempt' => 1]));
    }
}
