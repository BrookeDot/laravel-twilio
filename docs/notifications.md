# Notifications

Messages can be sent as part of Laravel's [notifications system](https://laravel.com/docs/notifications). A notifiable (such as a User model) should include the "twilio" channel in its `via()` method. When routing the notification, the phone number the message should be sent to should be returned.

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Get the notification's delivery channels.
     *
     * @return string[]
     */
    public function via(mixed $notifiable): array
    {
        // This application's users can receive notifications by mail and Twilio SMS
        return ['mail', 'twilio'];
    }

    /**
     * Get the notification routing information for the Twilio driver.
     */
    public function routeNotificationForTwilio(Notification $notification): string
    {
        return $this->mobile_number;
    }
}
```

For notifications that support being sent as an SMS, you should define a `toTwilio` method on the notification class. This method will receive a $notifiable entity and should return a string containing the message text.

```php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

final class PasswordExpiredNotification extends Notification
{
    /**
     * Get the Twilio / SMS representation of the notification.
     *
     * @param User $notifiable
     */
    public function toTwilio(mixed $notifiable): string
    {
        return sprintf('Hello %s, this is a note that the password for your %s account has expired.', $notifiable->name, config('app.name'));
    }
}
```

Your default connection is used for the notification channel by default. If your application utilizes multiple Twilio API connections, you can set the connection which should be used using the `TWILIO_NOTIFICATION_CHANNEL_CONNECTION` environment variable.
