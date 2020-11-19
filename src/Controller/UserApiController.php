<?php

namespace App\Controller;

use SplFileInfo;
use App\Entity\User;
use App\Entity\Travel;
use App\Entity\Picture;
use App\Repository\UserRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserApiController extends AbstractController
{

    /**
     * @Route("/api/login/register", name="user_register", methods={"POST"})
     */
    public function register(UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, MailerInterface $mailerInterface, MailerController $mailerController)
    {

        // Deserialize Json into User object
        try {
            $user = $serializer->deserialize(
                $request->getContent(),
                User::class,
                'json'
            );
        } catch (NotEncodableValueException $exception) {
            return $this->json(
                [
                    "success" => false,
                    "errors" => $exception->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return $this->json(
                [
                    "success" => false,
                    "errors" => $errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Encode the password
        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $user->setToken(md5(uniqid()));
        
        // Save user in DB
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

        // Send email to user for activate account
        $mailerController->sendEmail(
            $mailerInterface, $user->getEmail(), 
            'Merci de vous inscrire!', 
            'emails/activation.twig.html', 
            $user->getFirstName(), 
            $user->getToken()
        );

        
        return $this->json(
            [
                "success" => true,
                "id" => $user->getId()
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/activation/{token}", name="activation", methods={"GET"})
     */
    public function activation($token, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['token' => $token]);

        if (!$user) {
            return $this->json(
                [
                    "success" => false,
                    "errors" => 'Cet utilisateur n\'existe pas.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->setToken(null);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

   	return $this->redirect('http://34.202.233.128/login');
    }

    /**
     * @Route("/api/admin/user/list", name="user_list", methods={"GET"})
     */
    public function list(UserRepository $userRepository)
    {
        $userList = $userRepository->findAll();

        return $this->json(
            $userList,
            200,
            [],
            ["groups" => ["user"]]
        );
    }

    /**
     * @Route("/api/user", name="user", methods={"GET"})
     */
    public function user(UserRepository $userRepository, JWTEncoderInterface $jWTEncoderInterface)
    {
        //we put the token of the header in a variable
        $token = substr(apache_request_headers()["Authorization"], 7);
        //we decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
        //we recover the Id of the connected user
        $userConnectedId = $userRepository->findOneByUsername($tokenArray['username'])->getId();
        $user = $userRepository->find($userConnectedId);
        //we recover the roles of the connected user in a variable
        $userRoles = $tokenArray["roles"];
        //we create a boolean for the autorization of the user
        $userAuthorization = false;
        //for each role of the user
        foreach ($userRoles as $role) {
            //if the role is equal to "ROLE_ADMIN"
            if ($role == "ROLE_ADMIN") {
                //we put the boolean to True
                $userAuthorization = true;
            }
        }


        //if the user is exist or user is admin
        if ($userConnectedId or $userAuthorization == true) {
            //we return the informations of the user
            return $this->json(
                $user,
                200,
                [],
                ["groups" => ["user:information"]]
            );
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not this user and you're not an Administrator"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * @Route("/api/user/{id}/update", name="user_update", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function update(UserRepository $userRepository, $id, JWTEncoderInterface $jWTEncoderInterface, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $manager = $this->getDoctrine()->getManager();
        //we recover the user object with the $id of the URL
        $user = $userRepository->find($id);
        //we put the token of the header in a variable
        $token = substr(apache_request_headers()["Authorization"], 7);
        //we decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
        //we recover the Id of the connected user
        $userConnectedId = $userRepository->findOneByUsername($tokenArray['username'])->getId();

        //if the id of the connected user is same of the id in the URL
        if ($userConnectedId == $id) {
            //transforms JSON content into Array
            $requestArray = json_decode($request->getContent(), true);

            //we save the date of the update
            $user->setUpdatedAt(new \DateTime());

            //if the request contains a lastname, we replace the old one with the lastname of the request
            if (array_key_exists('lastName', $requestArray) && $requestArray['lastName'] != null) {
                $user->setLastName($requestArray['lastName']);
            }

            //if the request contains a firstname, we replace the old one with the firstname of the request
            if (array_key_exists('firstName', $requestArray) && $requestArray['firstName'] != null) {
                $user->setFirstName($requestArray['firstName']);
            }

            //if the request contains a avatar, we replace the old one with the avatar of the request
            if (array_key_exists('avatar', $requestArray) && $requestArray['avatar'] != null) {
                $oldAvatar = $user->getAvatar();
                if (!empty($oldAvatar)) {
                    unlink(__DIR__ . "/../../public/uploads/pictures/" . $oldAvatar);
                }


                //we create a new FileSystem object
                $fileSystem = new Filesystem();
                //we recover the uploaded file
                /** @var UploadedFile $avatar */
                //we recover the data and the url in variables
                $avatarFile = $requestArray['avatar']['data'];
                $avatarUrl = $requestArray['avatar']['url'];
                //we create a unique picture name with the extension of $pictureUrl
                $pictureName = uniqid() . strrchr($avatarUrl, '.');
                $spl = new SplFileInfo($pictureName);
                //we put the extension in a variable
                $extension = $spl->getExtension();
                //we test if the extension is "jpeg" or "jpg" or "png"
                //if it isn't
                if ($extension != "jpeg" and $extension != "png" and $extension != "jpg") {
                    //we return an error
                    return $this->json(
                        [
                            "success" => false,
                            "error" => "Bad extension file"
                        ],
                        Response::HTTP_NOT_ACCEPTABLE
                    );
                }
                //if it is
                else {
                    //we prepare the way for store the pictures
                    $current_dir_path = getcwd() . "/uploads/pictures/";
                    //we decode the data picture
                    $decodePicture = base64_decode($avatarFile);
                    //we create the repository of the step and put the picture in this repository
                    $fileSystem->dumpFile($current_dir_path . $pictureName, $decodePicture);
                    $user->setAvatar($pictureName);
                }
            }

            //if the request contains a password, we replace the old one with the password of the request
            if (array_key_exists('password', $requestArray) && $requestArray['password'] != null) {
                $password = $passwordEncoder->encodePassword($user, $requestArray['password']);
                $user->setPassword($password);
            }
            // if everything is ok then we save the object in Database
            $manager->persist($user);
            $manager->flush();

            // we return confirmation message of everything is OK
            return $this->json(
                [
                    "success" => true,
                    "id" => $user->getId()
                ],
                Response::HTTP_OK
            );
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not this user"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * @Route("api/user/{id}/delete", name="api_user_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(UserRepository $userRepository, $id, JWTEncoderInterface $jWTEncoderInterface)
    {
        //we put the token of the header in a variable
        $token = substr(apache_request_headers()["Authorization"], 7);
        //we decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
        //we recover the roles of the connected user in a variable
        $userRoles = $tokenArray["roles"];
        //we recover the Id of the connected user
        $userId = $userRepository->findOneByUsername($tokenArray['username'])->getId();
        //we create a boolean for the autorization of the user
        $userAuthorization = false;
        //for each role of the user
        foreach ($userRoles as $role) {
            //if the role is equal to "ROLE_ADMIN"
            if ($role == "ROLE_ADMIN") {
                //we put the boolean to True
                $userAuthorization = true;
            }
        }

        //if the Id of the connected user is same of the Id of the creator of the travel
        if ($userAuthorization == true or $id == $userId) {
            $manager = $this->getDoctrine()->getManager();
            //we select the desired user object with the url id
            $user = $userRepository->find($id);
            //we recover the roles of the user
            $userRoles = $user->getRoles();
            //we create a boolean for know if the user is an Admin
            $userAdmin = false;
            //for each role of $userRoles
            foreach ($userRoles as $role) {
                //if the role is "ROLE_ADMIN"
                if ($role == "ROLE_ADMIN") {
                    //we put the boolean for know if the user is an Admin to "True"
                    $userAdmin = true;
                }
            }

            //if the boolean for know if the user is an Admin to "True"
            if ($userAdmin) {
                //we return an error with "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're can't delete an Administrator account"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            } else {
                //else we remove the user and save the change in Database
                $manager->remove($user);
                $manager->flush();

                //we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true
                    ],
                    Response::HTTP_OK
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not this user and you're not an Administrator"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
