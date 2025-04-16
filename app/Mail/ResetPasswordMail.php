<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    /**
     * Tạo instance mới cho mailable.
     */
    public function __construct($email, $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Xây dựng email gửi đi.
     */
    public function build()
    {
        return $this->subject('Yêu cầu đặt lại mật khẩu')
            ->view('emails.reset_password')
            ->with([
                'token' => $this->token,
                'email' => $this->email,
            ]);
    }
}