<?php

namespace App\Services;

// use Symfony\Component\Mailer\Exception\TransportException;

use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Symfony\Component\Mailer\Transport\TransportInterface;


class MailerService {

    private $mailer;
    private $twig;
    private TransportInterface $transport;

    public function __construct(MailerInterface $mailer, 
    Environment $twig, TransportInterface $transport)
    {
        $this->mailer = $mailer ;
        $this->twig = $twig;
        $this->transport = $transport;

    }

    /**
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param string $template
     * @param array $parameters
     * @throws TransportExceptionInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMail(string $from, string $to,  string $subject, string $template, array $parameters): void
    {   
        try {

            $email = (new Email())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->text('text')
                ->html(
                     $this->twig->render($template, $parameters)
                );

            $this->transport->send($email);
        } catch (TransportException $e) {
            print $e->getMessage()."\n";
            throw $e;
        }
    }
}