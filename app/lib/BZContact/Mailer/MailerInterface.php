<?php
namespace BZContact\Mailer;

interface MailerInterface
{
    public function send(array $data);
}
