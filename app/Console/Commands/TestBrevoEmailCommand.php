<?php

namespace App\Console\Commands;

use App\Services\BrevoMailService;
use Illuminate\Console\Command;
use RuntimeException;

class TestBrevoEmailCommand extends Command
{
    protected $signature = 'nutritrace:test-email
                            {recipient? : Email address to send the test to (defaults to ADMIN_NOTIFICATION_EMAIL)}';

    protected $description = 'Diagnose the Brevo transactional email configuration and send a test email.';

    public function handle(): int
    {
        $this->output->title('NutriTrace Email Diagnostic');
        $this->output->writeln(str_repeat('-', 50));

        $apiKey = config('services.brevo.api_key');
        $senderEmail = config('services.brevo.sender_email');
        $senderName = config('services.brevo.sender_name', 'NutriTrace');
        $adminEmail = config('services.brevo.admin_email');
        $endpoint = config('services.brevo.endpoint', 'https://api.brevo.com/v3/smtp/email');

        $apiConfigured = filled($apiKey);
        $senderConfigured = filled($senderEmail) && filter_var($senderEmail, FILTER_VALIDATE_EMAIL);
        $adminConfigured = filled($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL);

        $this->output->writeln(sprintf('  <info>Brevo API key:</info>   %s', $apiConfigured ? '<comment>configured (' . $this->maskSecret((string) $apiKey) . ')</comment>' : '<error>MISSING ❌</error>'));
        $this->output->writeln(sprintf('  <info>Sender:</info>         %s', $senderConfigured ? "<comment>{$senderName} <{$senderEmail}></comment>" : '<error>MISSING or invalid ❌</error>'));
        $this->output->writeln(sprintf('  <info>Admin email:</info>    %s', $adminConfigured ? "<comment>{$adminEmail}</comment>" : '<fg=yellow>Not configured</>'));
        $this->output->writeln(sprintf('  <info>Endpoint:</info>       %s', $endpoint));
        $this->output->newLine();

        $recipient = $this->argument('recipient') ?? $adminEmail;

        if (! filled($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->output->writeln(sprintf('<error>  No valid recipient provided. Either pass one as argument or configure ADMIN_NOTIFICATION_EMAIL.</error>'));
            $this->output->writeln(sprintf('  Usage: <comment>php artisan nutritrace:test-email you@example.com</comment>'));

            return self::INVALID;
        }

        $this->output->writeln(sprintf('  <info>Recipient:</info>      <comment>%s</comment>', $recipient));
        $this->output->newLine();

        if (! $apiConfigured || ! $senderConfigured) {
            $this->output->error('Brevo configuration is incomplete. Fill BREVO_API_KEY and BREVO_SENDER_EMAIL in .env and run `php artisan config:clear`.');

            return self::FAILURE;
        }

        $this->output->writeln('  Sending test email...');
        $this->output->newLine();

        try {
            $messageId = app(BrevoMailService::class)->sendTestEmail((string) $recipient);
            $this->output->success('Test email delivered to Brevo transactional API.');
            $this->output->writeln(sprintf('  <info>Brevo message id:</info> <comment>%s</comment>', $messageId));

            return self::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->output->error('FAILED');
            $this->output->newLine();
            $message = $exception->getMessage();
            $status = $exception->getCode() > 0 ? $exception->getCode() : 'n/a';
            $this->output->writeln(sprintf('  <info>Brevo HTTP status:</info> <comment>%s</comment>', $status));
            $this->output->writeln(sprintf('  <info>Reason:</info>            %s', $message));

            if ($status === 401 || str_contains(strtolower($message), 'api key') || str_contains(strtolower($message), 'unauthorized')) {
                $this->output->writeln('  <fg=yellow>→ Your BREVO_API_KEY is probably wrong or disabled in the Brevo dashboard.</>');
            }

            $this->output->newLine(2);
            $this->output->writeln('  Check <comment>storage/logs/laravel.log</comment> for more details.');

            return self::FAILURE;
        }
    }

    private function maskSecret(string $secret): string
    {
        $len = mb_strlen($secret);
        if ($len <= 8) {
            return str_repeat('*', max($len, 4));
        }

        return mb_substr($secret, 0, 4) . str_repeat('*', max($len - 8, 4)) . mb_substr($secret, -4);
    }
}
