<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BrevoMailService
{
    public function sendWelcomeConsumerEmail(User $user): string
    {
        return $this->send(
            $user,
            'Your NutriTrace account is ready',
            view('emails.welcome-consumer', compact('user'))->render(),
            'welcome_consumer',
        );
    }

    public function sendRegistrationReceivedEmail(User $user): string
    {
        return $this->send(
            $user,
            'Your NutriTrace application has been received',
            view('emails.registration-received', compact('user'))->render(),
            'registration_received',
        );
    }

    public function sendAdminNewVerificationRequestEmail(User $user, VerificationRequest $verificationRequest): string
    {
        return $this->sendToAdmin(
            'New NutriTrace verification request',
            view('emails.admin-new-verification', compact('user', 'verificationRequest'))->render(),
            'admin_new_verification_request',
        );
    }

    public function sendAdminInformationSubmittedEmail(User $user, VerificationRequest $verificationRequest): string
    {
        return $this->sendToAdmin(
            'NutriTrace application resubmitted',
            view('emails.admin-information-submitted', compact('user', 'verificationRequest'))->render(),
            'admin_information_submitted',
        );
    }

    public function sendAccountActivatedEmail(User $user): string
    {
        return $this->send(
            $user,
            'Your NutriTrace account is now active',
            view('emails.account-activated', compact('user'))->render(),
            'account_activated',
        );
    }

    public function sendInformationRequestEmail(User $user, string $reason): string
    {
        return $this->send(
            $user,
            'Additional information required for your NutriTrace application',
            view('emails.information-required', compact('user', 'reason'))->render(),
            'information_required',
        );
    }

    public function sendApprovalEmail(User $user, string $activationUrl): string
    {
        return $this->send(
            $user,
            'Your NutriTrace account has been approved',
            view('emails.account-approved', compact('user', 'activationUrl'))->render(),
            'account_approved',
        );
    }

    public function sendRejectionEmail(User $user, string $reason): string
    {
        return $this->send(
            $user,
            'Your NutriTrace application has been rejected',
            view('emails.account-rejected', compact('user', 'reason'))->render(),
            'account_rejected',
        );
    }

    public function sendSuspensionEmail(User $user, ?string $reason = null): string
    {
        return $this->send(
            $user,
            'Your NutriTrace account has been suspended',
            view('emails.account-suspended', compact('user', 'reason'))->render(),
            'account_suspended',
        );
    }

    public function sendReactivationEmail(User $user): string
    {
        return $this->send(
            $user,
            'Your NutriTrace account has been reactivated',
            view('emails.account-reactivated', compact('user'))->render(),
            'account_reactivated',
        );
    }

    public function sendTestEmail(string $recipient): string
    {
        return $this->sendTo(
            $recipient,
            'NutriTrace Brevo test email',
            view('emails.test')->render(),
            'test_email',
        );
    }

    private function send(User $user, string $subject, string $htmlContent, string $type): string
    {
        return $this->sendTo($user->email, $subject, $htmlContent, $type, $user->name);
    }

    private function sendToAdmin(string $subject, string $htmlContent, string $type): string
    {
        $adminEmail = config('services.brevo.admin_email');

        if (blank($adminEmail)) {
            Log::warning('NutriTrace admin notification email is not configured.', [
                'type' => $type,
                'subject' => $subject,
            ]);

            throw new RuntimeException('The administrator notification email is not configured.');
        }

        return $this->sendTo($adminEmail, $subject, $htmlContent, $type, config('services.brevo.sender_name', 'NutriTrace Admin'));
    }

    private function sendTo(string $recipient, string $subject, string $htmlContent, string $type, ?string $recipientName = null): string
    {
        $apiKey = config('services.brevo.api_key');
        $senderEmail = config('services.brevo.sender_email');
        $senderName = config('services.brevo.sender_name', 'NutriTrace');

        if (blank($apiKey) || blank($senderEmail)) {
            Log::error('Brevo email delivery is not configured.', [
                'type' => $type,
                'recipient_domain' => $this->maskEmail($recipient),
                'missing' => blank($apiKey) ? 'BREVO_API_KEY' : 'BREVO_SENDER_EMAIL',
            ]);

            throw new RuntimeException(sprintf(
                'Brevo email delivery is not configured: %s is missing.',
                blank($apiKey) ? 'BREVO_API_KEY' : 'BREVO_SENDER_EMAIL',
            ));
        }

        $to = [filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'email' : 'email' => $recipient];
        if (filled($recipientName)) {
            $to['name'] = $recipientName;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->timeout(15)->post(config('services.brevo.endpoint'), [
                'sender' => ['email' => $senderEmail, 'name' => $senderName],
                'to' => [$to],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

            if ($response->failed()) {
                $message = $this->responseMessage($response);
                Log::error('Brevo transactional email failed.', [
                    'type' => $type,
                    'status' => $response->status(),
                    'recipient_domain' => $this->maskEmail($recipient),
                    'message' => $message,
                ]);

                throw new RuntimeException(sprintf(
                    'Brevo rejected the email request. HTTP status: %d. Message: %s',
                    $response->status(),
                    $message,
                ), $response->status());
            }

            $messageId = (string) ($response->json('messageId') ?? 'accepted');
            Log::info('Brevo transactional email accepted.', [
                'type' => $type,
                'message_id' => $messageId,
                'recipient_domain' => $this->maskEmail($recipient),
            ]);

            return $messageId;
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Brevo transactional email request failed.', [
                'type' => $type,
                'recipient_domain' => $this->maskEmail($recipient),
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Brevo email delivery failed: ' . $exception->getMessage(), previous: $exception);
        }
    }

    private function responseMessage(\Illuminate\Http\Client\Response $response): string
    {
        $message = $response->json('message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        $code = $response->json('code');

        if (is_string($code) && $code !== '') {
            return $code;
        }

        return 'Brevo returned an error without a message.';
    }

    private function maskEmail(string $email): string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '***invalid***';
        }

        [$local, $domain] = explode('@', $email, 2);
        $localLen = mb_strlen($local);

        if ($localLen <= 2) {
            $maskedLocal = str_repeat('*', $localLen);
        } else {
            $maskedLocal = mb_substr($local, 0, 1) . str_repeat('*', max($localLen - 2, 1)) . mb_substr($local, -1);
        }

        return $maskedLocal . '@' . $domain;
    }
}