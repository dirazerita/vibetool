<?php

namespace App\Mail;

use App\Http\Controllers\Dashboard\EmailVerificationController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Email VibeTool.id',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.email-verification-code',
            with: [
                'name' => $this->user->name,
                'code' => $this->code,
                'minutes' => EmailVerificationController::CODE_TTL_MINUTES,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
