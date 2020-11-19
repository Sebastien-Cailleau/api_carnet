<?php

namespace App\Service;

use App\Entity\Travel;
use App\Repository\StepRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class CheckAuthorization
{

    /**
     * @param JWTEncoderInterface $jWTEncoderInterface
     * @param UserRepository $userRepository
     * @param Travel $travel
     * @return userArray [user, creator(false or true), admin(false or true), connected(false or true), follow(false or true)] or null (Bab Token or username not found)
     */
    public static function checkAuthorization(JWTEncoderInterface $jWTEncoderInterface, UserRepository $userRepository, Travel $travel)
    {
        // get the Token from the headers
        $token = apache_request_headers()["Authorization"];
        //if there is no token
        if ($token == null) {
            //we return "null"
            return null;
        } 
        //we delete sub string "Berear "
        $token = substr($token, 7);
        //we Decode the token
        $tokenArray = $jWTEncoderInterface->decode($token);
        //we recover the user object by the username
        $user = $userRepository->findOneByUsername($tokenArray['username']);
        //if there is no user
        if ($user == null) {
            //we return "null"
            return null;
        } 
        //we put the $user object in $userArray["user"]
        $userArray["user"] = $user;
        //we put the value false in $userArray["creator"]
        $userArray["creator"] = false;
        //if the id of the user is same of the id of the creator of the travel
        if ($user->getId() == $travel->getCreator()->getId()) {
            //we put the value true in $userArray["creator"]
            $userArray["creator"] = true;
        }
        //we put the value false in $userArray["admin"]
        $userArray["admin"] = false;
        //we put the value false in $userArray["connected"]
        $userArray["connected"] = false;
        //we recover the roles of the connected user 
        $arrayRoles = $user->getRoles();
        //for each role of $arrayRoles
        foreach ($arrayRoles as $roles) {
            //if the connected user is an administrator, we put the value true in $userArray["admin"]
            if ($roles == "ROLE_ADMIN") $userArray["admin"] = true;
            //if the user is connected, we put the value true in $userArray["connected"]
            if ($roles == "ROLE_USER") $userArray["connected"] = true;
        }
        //we recover the travel objects that the user follows 
        $followTravels = $user->getFollower()->getValues();
        $followTravelsId = array();
        //for each travel of $followTravels
        foreach ($followTravels as $travel) {
            //we add the id of the travel in $followTravelsId
            array_push($followTravelsId, $travel->getId());
        }
        //we put the value false in $userArray["follow"]
        $userArray["follow"] = false;
        //for each id of $followTravelsId
        foreach ($followTravelsId as $travelId) {
            //if the id is same of the id of the travel
            if ($travel->getId() == $travelId) {
                //we put the value true in $userArray["follow"]
                $userArray["follow"] = true;
            }
        }
        //we return the data of $userArray
        return $userArray;
    }

    /**
     * @param travelId $travelId
     * @param stepId $stepId
     * @param StepRepository $stepRepository
     * @return match True or False (if the step matches to the travel)
     */
    public static function checkTravelStep($travelId, $stepId, StepRepository $stepRepository)
    {
        //we put the value false in $match
        $match = false;
        //if this step is a part of this travel
        if ($stepRepository->find($stepId)->getTravel()->getId() == $travelId) {
            //we put the value true in $match
            $match = true;
        }
        //we return $match
        return $match;
    }
}
