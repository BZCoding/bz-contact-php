<?php
namespace BZContact\Mailer;

interface MailerInterface
{
    public function send(array $data);
    public function sendAdminNotification(array $message);
    public function sendSubscriberNotification(array $message);
}
