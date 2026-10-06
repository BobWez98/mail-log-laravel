<?php

declare(strict_types=1);

namespace BobWez98\MailLog\Listeners;

use BobWez98\MailLog\Contracts\UpdatesMailLog;
use BobWez98\MailLog\Data\UpdateMailLogData;
use BobWez98\MailLog\Enums\LogStatus;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Email;
use Throwable;

class MessageSentListener
{
    public function __construct(
        protected UpdatesMailLog $updateMailLog
    ) {}

    public function handle(MessageSent $event): void
    {
        try {
            /** @var Email $message */
            $message = $event->sent->getOriginalMessage();
            $header = $message
                ->getHeaders()
                ->get('X-Mail-Log-ID');

            if (! $header) {
                return;
            }

            $uuid = $header->getBodyAsString();

            $data = UpdateMailLogData::new([
                'message_id' => $uuid,
                'data' => $event->data,
                'status' => LogStatus::SUCCESS,
                'sent_at' => now(),
            ]);

            $this->updateMailLog->update($data);
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
