<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Enums;

enum LogStatus: string
{
    case SUCCESS = 'success';
    case PENDING = 'pending';
}
