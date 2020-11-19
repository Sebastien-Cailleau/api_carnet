<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;



class SecurityController extends AbstractController
{
    /**
     * 
     * @Route("/api/login/checkactivation", name="api_login_checkactivation")
     * 
     */
    public function apiCheckActivation(Request $request, UserRepository $userRepository)
    {
        $userData = json_decode($request->getContent(), true);

        $user = $userRepository->findOneBy(['email' => $userData['username']]);

        if (!$user) {
            return $this->json(
                [
                    "success" => false,
                    "errors" => "Vous n'avez pas de compte, merci de vous inscrire"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($user->getToken()) {
            return $this->json(
                [
                    "success" => false,
                    "errors" => "Votre compte n'est pas activé, vérifiez vos mail"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }


   	return $this->json(
            $userData,
            200,
            []
        );
    }

    /**
     * @Route("/api/login_check", name="app_api_login_check", methods={"POST"})
     */
    public function apiLoginCheck()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/logout", name="app_logout")
     *
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/email-forgot-password", name="app_forgotten_password", methods={"POST"})
     *
     */
    public function emailForgotPassword(Request $request, UserRepository $userRepository, MailerInterface $mailerInterface, MailerController $mailerController, TokenGeneratorInterface $tokenGenerator)
    {
        //transforms JSON content into Array
        $requestArray = (json_decode($request->getContent(), true));
        //we recover the object user by the email of the request
        $user = $userRepository->findOneByUsername($requestArray['email']);
        //if the user doesn't exist
        if ($user === null) {
            //we return an error with "HTTP_BAD_REQUEST"
            return $this->json(
                [
                    "success" => false,
                    "errors" => "Unknown email"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
        //we generate a new token
        $token = $tokenGenerator->generateToken();

        //we save the token in the database
        try {
            $user->setToken($token);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();
        } catch (NotEncodableValueException $exception) {

            // we then send an error to the one who calls the API
            return $this->json(
                [
                    "success" => false,
                    "error" => $exception->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        //we send an email
        $mailerController->sendEmail(
            $mailerInterface,
            //to the email of the user
            $user->getEmail(),
            //with the subject
            'Réinitialisation de mot de passe',
            //the email contain
            'emails/reset_password.twig.html',
            //the username is the firstname of the user
            $user->getFirstName(),
            //the token is the token of the user
            $user->getToken()
        );

        // we return confirmation message of everything is OK
        return $this->json(
            [
                "success" => true
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password", methods={"POST"})
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        //transforms JSON content into Array
        $requestArray = json_decode($request->getContent(), true);
        //we recover the object user by the token
        $user = $userRepository->findOneBy(['token' => $token]);
        //if the user doesn't exist
        if ($user === null) {
            //we return an error with "HTTP_BAD_REQUEST"
            return $this->json(
                [
                    "success" => false,
                    "errors" => "Unknown token"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        //we put the token to null
        $user->setToken(null);
        //we save the new password after encode this
        $user->setPassword($passwordEncoder->encodePassword($user, $requestArray['password']));
        //we save the date of the update
        $user->setUpdatedAt(new \DateTime());

        // if everything is ok then we save the object in Database
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();


        // we return confirmation message of everything is OK
        return $this->json(
            [
                "success" => true
            ],
            Response::HTTP_OK
        );
    }
}
