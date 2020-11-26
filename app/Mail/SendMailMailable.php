<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $input;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input=[])
    {
        $this->input = $input['data'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = (!empty($this->input['subject'])?$this->input['subject']:'');
        return $this->view($this->input['template'], $this->input)->subject($subject);
    }
}
