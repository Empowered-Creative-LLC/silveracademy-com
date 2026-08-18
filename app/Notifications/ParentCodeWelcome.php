<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\Mail;
use SendGrid;

class ParentCodeWelcome extends Notification
{
    /**
     * The plain text temporary password.
     */
    protected string $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $this->sendViaSendGrid($notifiable);

        return [];
    }

    /**
     * Send the email via SendGrid API (or log if not configured).
     */
    protected function sendViaSendGrid(object $notifiable): void
    {
        $apiKey = config('services.sendgrid.api_key');
        if (empty($apiKey)) {
            Log::info('ParentCodeWelcome: SendGrid not configured, skipping email', [
                'email' => $notifiable->email,
                'password_preview' => substr($this->password, 0, 2) . '***',
            ]);
            return;
        }

        try {
            $sendgrid = new SendGrid($apiKey);
            $email = new Mail();
            $email->setFrom(
                config('services.sendgrid.from_email'),
                config('services.sendgrid.child_portal_from_name', 'Silver Academy Child Portal')
            );
            $email->setSubject('Your Portal Login Details');
            $email->addTo($notifiable->email, $notifiable->name);

            $loginUrl = url('/login');

            $content = "Hello {$notifiable->name}!\n\n";
            $content .= "You used a Parent Code to register for the Silver Academy Family Portal.\n\n";
            $content .= "Your login details:\n\n";
            $content .= "Email: {$notifiable->email}\n";
            $content .= "Temporary password: {$this->password}\n\n";
            $content .= "Log in here: {$loginUrl}\n\n";
            $content .= "For security, we recommend changing your password after your first login (Settings after logging in).\n\n";
            $content .= "If you receive another Parent Code for another child, you can link them after logging in in Settings → Linked Students (enter the code and click Add child).\n\n";
            $content .= "If you have any questions, please contact the school office.\n\n";
            $content .= "Warm regards,\nSilver Academy";

            $email->addContent("text/plain", $content);

            $escapedName = e($notifiable->name);
            $escapedEmail = e($notifiable->email);
            $escapedPassword = e($this->password);

            $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px;'>Silver Academy Family Portal</h1>
                </div>
                <div style='padding: 30px; background: #ffffff;'>
                    <h2 style='color: #1e3a5f; margin-top: 0;'>Hello {$escapedName}!</h2>
                    <p style='color: #333; font-size: 16px; line-height: 1.6;'>
                        You used a Parent Code to register for the Family Portal.
                    </p>
                    <div style='background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                        <h3 style='color: #1e3a5f; margin-top: 0; margin-bottom: 15px;'>Your Login Details</h3>
                        <p style='margin: 8px 0; color: #333;'><strong>Email:</strong> {$escapedEmail}</p>
                        <p style='margin: 8px 0; color: #333;'><strong>Temporary password:</strong> <code style='background: #e9ecef; padding: 2px 8px; border-radius: 4px; font-size: 16px; letter-spacing: 0.03em;'>{$escapedPassword}</code></p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$loginUrl}' style='display: inline-block; background: #f5a623; color: #1e3a5f; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-weight: bold; font-size: 16px;'>Log In to Portal</a>
                    </div>
                    <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                        For security, we recommend changing your password after your first login (Settings after logging in).
                    </p>
                    <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                        If you receive another Parent Code for another child, you can link them after logging in in <strong>Settings → Linked Students</strong> (enter the code and click Add child).
                    </p>
                    <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                        If you have any questions, please contact the school office.
                    </p>
                </div>
                <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e9ecef;'>
                    <p style='color: #666; font-size: 14px; margin: 0;'>
                        Warm regards,<br>
                        <strong style='color: #1e3a5f;'>Silver Academy</strong>
                    </p>
                </div>
            </div>";

            $email->addContent("text/html", $htmlContent);

            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                Log::info('ParentCodeWelcome email sent', ['user_id' => $notifiable->id, 'email' => $notifiable->email]);
            } else {
                Log::error('SendGrid error sending ParentCodeWelcome', [
                    'user_id' => $notifiable->id,
                    'email' => $notifiable->email,
                    'status_code' => $response->statusCode(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending ParentCodeWelcome email', [
                'user_id' => $notifiable->id,
                'email' => $notifiable->email,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'parent_code_welcome'];
    }
}
