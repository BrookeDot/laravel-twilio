<?php

namespace BabDev\Twilio\Notifications\Channels;

use BabDev\Twilio\Contracts\TwilioClient;
use Illuminate\Notifications\Notification;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

final class TwilioChannel
{
    /**
     * @var TwilioClient
     */
    private $twilio;

    /**
     * Creates a new Twilio notification channel.
     *
     * @param TwilioClient $twilio The Twilio client.
     */
    public function __construct(TwilioClient $twilio)
    {
        $this->twilio = $twilio;
    }

    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @return MessageInstance|null
     *
     * @throws TwilioException on Twilio API failure
     */
    public function send($notifiable, Notification $notification): ?MessageInstance
    {
        $to = $notifiable->routeNotificationFor('twilio', $notification);

        if (!$to) {
            return null;
        }

        $message = $notification->toTwilio($notifiable);

        if (!$message) {
            return null;
        }

        return $this->twilio->message($to, $message);
    }
}
