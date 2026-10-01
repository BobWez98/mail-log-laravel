<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Listeners;

use BobWez98\MailLog\Contracts\CreatesMailLog;
use BobWez98\MailLog\Data\CreateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;

class MessageSendingListener
{
    public function __construct(
        protected CreatesMailLog $createMailLog
    ) {}

    public function handle(MessageSending $event): void
    {
        $uuid = (string) Str::uuid();

        $event->message
            ->getHeaders()
            ->addTextHeader('X-Mail-Log-ID', $uuid);

        $data = CreateMailLogData::new([
            'message_id' => $uuid,
            'status' => LogStatus::PENDING,
            'from' => implode(', ', array_map(
                static fn (Address $address): string => $address->toString(),
                $event->message->getFrom(),
            )),
            'to' => implode(', ', array_map(
                static fn (Address $address): string => $address->toString(),
                $event->message->getTo(),
            )),
            'subject' => $event->message->getSubject(),
            'body' => $event->message->getHtmlBody() ?? $event->message->getTextBody(),
            'data' => $event->data,
        ]);

        $this->createMailLog->create($data);
    }
}
