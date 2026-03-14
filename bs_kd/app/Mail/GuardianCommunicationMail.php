<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuardianCommunicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $messageHtml;
    public string $studentName;
    public string $senderName;
    public string $schoolName;

    public function __construct(string $subjectLine, string $messageHtml, string $studentName, string $senderName, string $schoolName)
    {
        $this->subjectLine = $subjectLine;
        $this->messageHtml = $messageHtml;
        $this->studentName = $studentName;
        $this->senderName = $senderName;
        $this->schoolName = $schoolName;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.guardian-message');
    }
}
