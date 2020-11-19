<?php

namespace App\Controller;

use App\Repository\TravelRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\TravelApiController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @Route("/api/admin")
 */
class AdminApiController extends AbstractController
{
    /**
     * @Route("/userlist", name="api_admin_userlist", methods={"GET"})
     * 
     */
    public function userlist(UserRepository $userRepository, JWTEncoderInterface $jWTEncoderInterface)
    {
        //we put the token of the header in a variable
        $token = substr(apache_request_headers()["Authorization"], 7);
        //we decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
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

        //if the Id of the connected user is same of the Id of the creator of the travel
        if ($userAuthorization == true) {
            //create an array of all users
            $userlist = $userRepository->findAll();

            // we use the symfony serializer to transform objects into JSON
            return $this->json(
                $userlist,
                200,
                [],
                ["groups" => ["userlist"]]
            );
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not an Administrator"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * @Route("/userlist/search", name="api_admin_userlist_search", methods={"GET"})
     */
    public function user(UserRepository $userRepository, Request $request, JWTEncoderInterface $jWTEncoderInterface)
    {
        //we put the token of the header in a variable
        $token = substr(apache_request_headers()["Authorization"], 7);
        //we decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
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

        //if the Id of the connected user is same of the Id of the creator of the travel
        if ($userAuthorization == true) {
            //we recover the Json search and we decode in "string" 
            $search = (json_decode($request->getContent(), True)["search"]);
            //we recover all users who have the string "search" in their firstname or lastname
            $results = $userRepository->findByName($search);
            // we use the symfony serializer to transform objects into JSON
            return $this->json(
                $results,
                200,
                [],
                ["groups" => ["userlist:search"]]
            );
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not an Administrator"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
