<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class MailController extends BaseController
{
    // function for send email functionality 
    public function send_mail($to,$from,$from_message,$subject,$message)
    {
        $email = \Config\Services::email();
            $email->setTo($to);
            $email->setFrom($from, $from_message);
            $email->setSubject($subject);
            $email->setMessage($message);
            $email->send();
    }
}
