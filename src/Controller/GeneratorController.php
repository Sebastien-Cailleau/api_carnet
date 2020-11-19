<?php

namespace App\Controller;

use App\Entity\Travel;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Serializer\JsonEncoder;

class GeneratorController extends AbstractController
{
    /**
     * @Route("/api/generate_url/{id}", name="generate_url", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function generateUrlToken(Travel $travel = null)
    {

        // Return bad request if $travel = null
        if (!$travel) {
            return $this->json(
                [
                    "succes" => false,
                    "errors" => "Bad Travel id"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }


        // Return token and travelID if token exist in DB
        if ($travel->getToken()) {
            return $this->json(
                [
                    "id" => $travel->getId(),
                    "url_token" => $travel->getToken(),
                ],
                Response::HTTP_OK
            );
        }

        // Create token, save in DB and return token and travelID
        if (!$travel->getToken()) {
            $travel->setToken(md5(uniqid()));
            $travel->setTokenCreation(new DateTime('NOW'));
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($travel);
            $manager->flush();

            return $this->json(
                [
                    "id" => $travel->getId(),
                    "url_token" => $travel->getToken(),
                ],
                Response::HTTP_OK
            );
        }
    }
}
