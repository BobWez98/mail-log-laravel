<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Contracts;

use BobWez98\MailLog\Data\UpdateMailLogData;

interface UpdatesMailLog
{
    public function update(UpdateMailLogData $data): void;
}
