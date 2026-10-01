<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Tests\Enums;

use BobWez98\MailLog\Enums\LogStatus;
use BobWez98\MailLog\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class LogStatusTest extends TestCase
{
    #[Test]
    public function it_exposes_the_supported_statuses(): void
    {
        $this->assertSame(['success', 'pending', 'failed'], array_map(
            static fn (LogStatus $status): string => $status->value,
            LogStatus::cases(),
        ));
    }
}
