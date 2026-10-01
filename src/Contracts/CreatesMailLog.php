<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Contracts;

use BobWez98\MailLog\Data\CreateMailLogData;

interface CreatesMailLog
{
    public function create(CreateMailLogData $data): void;
}
