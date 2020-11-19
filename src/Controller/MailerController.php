<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MailerController extends AbstractController
{
    public function sendEmail(MailerInterface $mailer, $userMail, $subject, $html, $username, $token)
    {
        $email = (new TemplatedEmail())
            ->from('o.carnet.de.voyage@gmail.com')
            ->to($userMail)
            ->subject($subject)
            ->htmlTemplate($html)
            ->context([
                'username' => $username,
                'token' => $token
            ]);
            
        $mailer->send($email);
    }

    /**
     * @Route("/contact", name="contact", methods={"POST"})
     */
    public function sendEmailContact(MailerInterface $mailer, Request $request)
    {
        $requestArray = json_decode($request->getContent(), true);

        //Send email to preconfigure Mail
        $email = (new TemplatedEmail())
            ->from($requestArray["email"])
            ->to("o.carnet.de.voyage@gmail.com")
            ->subject($requestArray["object"] . " From: " . $requestArray["email"])
            ->text($requestArray["text"]);
            
        $mailer->send($email);

        //Send a copy
        $emailCopy = (new TemplatedEmail())
            ->from('o.carnet.de.voyage@gmail.com')
            ->to($requestArray["email"])
            ->subject('Copie du mail : ' . $requestArray["object"])
            ->text('Vous venez de nous faire parvenir ce message: '. PHP_EOL . $requestArray["text"]);

        $mailer->send($emailCopy);

	return $this->json(
            [
                "success" => true
            ],
            Response::HTTP_OK
        );
    }
}
