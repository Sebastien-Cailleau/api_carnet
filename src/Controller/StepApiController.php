<?php

namespace App\Controller;

use DateTime;
use SplFileInfo;
use App\Entity\Step;
use App\Entity\Travel;
use App\Entity\Picture;
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\StepRepository;
use App\Repository\TravelRepository;
use App\Repository\PictureRepository;
use App\Repository\CommentRepository;
use App\Service\CheckAuthorization;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;


/**
 *  @Route("/api/travel/{id}")
 */
class StepApiController extends AbstractController
{
    /**
     *  @Route("/step/{id2}", name="api_step_show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(StepRepository $stepRepository, $id2, JWTEncoderInterface $jWTEncoderInterface, UserRepository $userRepository, Travel $travel, CheckAuthorization $checkAuthorization)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            //if the connected user is an administrator or the creator of the travel or a follower of this travel
            if ($userArray["admin"] or $userArray["creator"] == true or $userArray["follow"]) {
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we return in JSON all the important variables for the display of the step pages
                return $this->json(
                    $step,
                    200,
                    [],
                    ["groups" => ["step:show"]]
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not the creator of the travel or an Administrator or a follower of the travel"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *  @Route("/add", name="api_step_add", methods={"POST"})
     */
    public function add(SerializerInterface $serializer, Request $request, ValidatorInterface $validator, Travel $travel, JWTEncoderInterface $jWTEncoderInterface, UserRepository $userRepository, CheckAuthorization $checkAuthorization)
    {
        //we recover an array with authorizations of the connected user
        $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
        $manager = $this->getDoctrine()->getManager();
        //if the connected user is the creator of this travel
        if ($userArray["creator"]) {
            try {
                // transforms the JSON to object of type step
                $step = $serializer->deserialize(
                    $request->getContent(),
                    Step::class,
                    'json',
                    ['attributes' => ['title', 'description', 'latitude', 'longitude']]
                );

                //If the content of the request is not correct JSON
                // the deserializer will throw an exception
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
            // Before persisting the object we check that its content is correct
            // we ask the validator to compare the properties of my object with the validation constraints (@Assert)
            $errors = $validator->validate($step);
            // if we found errors
            if ($errors->count() > 0) {
                // we return the errors found by the validator to the one who called the API
                return $this->json(
                    [
                        "success" => false,
                        "errors" => $errors
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
            //transforms JSON content into Array
            $requestArray = json_decode($request->getContent(), true);
            //add the date of the JSON request to the step object
            $date = DateTime::createFromFormat('j/m/Y', ($requestArray['step_date']));
            //add the date object to the step object
            $step->setStepDate($date);
            //add the travel object to the step object
            $step->setTravel($travel);
            //put the step_like to 0
            $step->setStepLike(0);
            //if the array "pictures" exists in the JSON request and it is not empty
            if (array_key_exists('pictures', $requestArray) && $requestArray['pictures'] != null) {
                //for each picture array in request
                for ($pictureJson = 0; $pictureJson < count($requestArray['pictures']); $pictureJson++) {
                    //we recover the uploaded file
                    /** @var UploadedFile $pictureFile */
                    //we recover the data and the url in variables
                    $pictureFile = $requestArray['pictures'][$pictureJson]['data'];
                    $pictureUrl = $requestArray['pictures'][$pictureJson]['url'];
                    //we create a unique name for each picture with the extension of $pictureUrl
                    $pictureName = uniqid() . strrchr($pictureUrl, '.');
                    $spl = new SplFileInfo($pictureName);
                    //we put the extension in a variable
                    $extension = strtolower($spl->getExtension());
                    //we test if the extension is "jpeg" or "jpg" or "png":
                    //-if it isn't
                    if ($extension != "jpeg" and $extension != "png" and $extension != "jpg") {
                        //we return an error
                        return $this->json(
                            [
                                "success" => false,
                                "error" => "Bad extension"
                            ],
                            Response::HTTP_BAD_REQUEST
                        );
                    }
                    //-if it is
                    else {
                        //we create a new FileSystem object
                        $fileSystem = new Filesystem();
                        //we prepare the path for store the pictures
                        $current_dir_path = getcwd() . "/uploads/pictures/";
                        //we decode the data picture
                        $decodePicture = base64_decode($pictureFile);
                        //we put the picture "public/uploads/pictures/"
                        $fileSystem->dumpFile($current_dir_path . $pictureName, $decodePicture);
                        //we create a new picture entity
                        $picture = new Picture();
                        //add the url and step to the picture entity
                        $picture->setUrl($pictureName);
                        $picture->setStep($step);
                        $manager->persist($picture);
                    }
                }
                //if there is no picture, we return a HTTP error "NOT_ACCEPTABLE"
            } else {
                return $this->json(
                    [
                        "success" => false,
                        "error" => "No picture"
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // if everything is ok then we save the object in Database
            $manager->persist($step);
            $manager->flush();

            // we return confirmation message of everything is OK with a HTTP_CREATED
            return $this->json(
                [
                    "success" => true,
                    "id" => $step->getId()
                ],
                Response::HTTP_CREATED
            );
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not the creator of the travel"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }


    /**
     *  @Route("/update/{id2}", name="api_step_update", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function update(Request $request, StepRepository $stepRepository, $id2, PictureRepository $pictureRepository, Travel $travel, JWTEncoderInterface $jWTEncoderInterface, UserRepository $userRepository, CheckAuthorization $checkAuthorization)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            $manager = $this->getDoctrine()->getManager();
            //if the connected user is the creator of this travel
            if ($userArray["creator"]) {
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we save the date of the update
                $step->setUpdatedAt(new \DateTime());
                //transforms JSON content into Array
                $requestArray = json_decode($request->getContent(), true);
                //if the array "pictures-delete" exists in the JSON request and it is not empty
                if (array_key_exists('pictures-delete', $requestArray) && $requestArray['pictures-delete'] != null) {
                    //we recover the pictures Id to delete
                    $picturesIdDelete = $requestArray['pictures-delete'];
                    //we create an array of object pictures to delete
                    $picturesDelete = array();
                    //for each id of the array $picturesIdDelete
                    foreach ($picturesIdDelete as $id) {
                        //we add objects picture with the id of the array $picturesIdDelete to the array $pictureDelete
                        array_push($picturesDelete, $pictureRepository->find($id));
                    }
                }
                //if the request contains a title, we replace the old one with the title of the request
                if (array_key_exists('title', $requestArray) && $requestArray['title'] != null) {
                    $step->setTitle($requestArray['title']);
                }

                //if the request contains a description, we replace the old one with the description of the request
                if (array_key_exists('description', $requestArray) && $requestArray['description'] != null) {
                    $step->setDescription($requestArray['description']);
                }

                //if the request contains a latitude, we replace the old one with the latitude of the request
                if (array_key_exists('latitude', $requestArray) && $requestArray['latitude'] != null) {
                    $step->setLatitude(($requestArray['latitude']));
                }

                //if the request contains a longitude, we replace the old one with the longitude of the request
                if (array_key_exists('longitude', $requestArray) && $requestArray['longitude'] != null) {
                    $step->setLongitude(($requestArray['longitude']));
                }

                //if the request contains a step_date, we replace the old one with the step_date of the request
                if (array_key_exists('step_date', $requestArray) && $requestArray['step_date'] != null) {
                    $date = DateTime::createFromFormat('j/m/Y', ($requestArray['step_date']));
                    $step->setStepDate($date);
                }

                //if the array pictures-delete exists in the JSON request and it is not empty
                if (array_key_exists('pictures-delete', $requestArray) && $requestArray['pictures-delete'] != null) {
                    foreach ($picturesDelete as $picture) {
                        //we delete the pictures of $picturesDelete of the database and of the file "pictures"
                        $step->removePicture($picture);
                        unlink(__DIR__ . "/../../public/uploads/pictures/" . $picture->getUrl());
                    }
                }
                //if the array "pictures-new" exists in the JSON request and it is not empty
                if (array_key_exists('pictures-new', $requestArray) && $requestArray['pictures-new'] != null) {
                    //for each picture array in request
                    for ($pictureJson = 0; $pictureJson < count($requestArray['pictures-new']); $pictureJson++) {
                        //we create a new FileSystem object
                        $fileSystem = new Filesystem();
                        //we recover the uploaded file
                        /** @var UploadedFile $pictureFile */
                        //we recover the data and the url in variables
                        $pictureFile = $requestArray['pictures-new'][$pictureJson]['data'];
                        $pictureUrl = $requestArray['pictures-new'][$pictureJson]['url'];
                        //we create a unique picture name with the extension of $pictureUrl
                        $pictureName = uniqid() . strrchr($pictureUrl, '.');
                        $spl = new SplFileInfo($pictureName);
                        //we put the extension in a variable
                        $extension = strtolower($spl->getExtension());
                        //we test if the extension is "jpeg" or "jpg" or "png"
                        //if it isn't
                        if ($extension != "jpeg" and $extension != "png" and $extension != "jpg") {
                            //we return an error
                            return $this->json(
                                [
                                    "success" => false,
                                    "error" => "Bad extension file"
                                ],
                                Response::HTTP_BAD_REQUEST
                            );
                        }
                        //if it is
                        else {
                            //we prepare the path for store the pictures
                            $current_dir_path = getcwd() . "/uploads/pictures/";
                            //we decode the data picture
                            $decodePicture = base64_decode($pictureFile);
                            //we put the picture "public/uploads/pictures/"
                            $fileSystem->dumpFile($current_dir_path . $pictureName, $decodePicture);
                            //we create a new picture entity
                            $picture = new Picture();
                            //add the url and step to the picture entity
                            $picture->setUrl($pictureName);
                            $picture->setStep($step);
                            $manager = $this->getDoctrine()->getManager();
                            $manager->persist($picture);
                        }
                    }
                }
                // if everything is ok then we save the object in Database
                $manager->persist($step);
                $manager->flush();
                // we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true,
                        "id" => $step->getId()
                    ],
                    Response::HTTP_OK
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not the creator of the travel"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *  @Route("/delete/{id2}", name="api_step_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(StepRepository $stepRepository, $id2, PictureRepository $pictureRepository, JWTEncoderInterface $jWTEncoderInterface, UserRepository $userRepository, Travel $travel, CheckAuthorization $checkAuthorization)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            //if the connected user is an administrator or the creator of this travel
            if ($userArray["admin"] or $userArray["creator"]) {
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we recover all the pictures linked to this step
                $pictures = $pictureRepository->findBy(['step' => $step->getId()]);
                //we delete every picture of $pictures
                foreach ($pictures as $picture) {
                    //we delete the old pictures of the database and of the file "pictures"
                    unlink(__DIR__ . "/../../public/uploads/pictures/" . $picture->getUrl());
                }
                $manager = $this->getDoctrine()->getManager();
                // we delete the step object and all that is linked to it
                $manager->remove($step);
                $manager->flush();

                //we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true
                    ],
                    Response::HTTP_OK
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not the creator of the travel and you're not an Administrator"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *  @Route("/like/{id2}", name="api_step_like", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function like(StepRepository $stepRepository, $id2, JWTEncoderInterface $jWTEncoderInterface, CheckAuthorization $checkAuthorization, Travel $travel, UserRepository $userRepository)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            //if the user is connected and follow this step
            if ($userArray["connected"]) {
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we recover the actual number of like
                $current_step_like = $step->getStepLike();
                //we add 1 to the number of like
                $new_step_like = $current_step_like + 1;
                //we save the new numbe rof like in step object
                $step->setStepLike($new_step_like);
                // if everything is ok then we save the change in Database
                $manager = $this->getDoctrine()->getManager();
                $manager->flush();
                //we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true
                    ],
                    Response::HTTP_OK
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not connected"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *  @Route("/unlike/{id2}", name="api_step_unlike", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function unlike(StepRepository $stepRepository, $id2, jWTEncoderInterface $jWTEncoderInterface, CheckAuthorization $checkAuthorization, Travel $travel, UserRepository $userRepository)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            //if the user is connected and follow this step
            if ($userArray["connected"]) {
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we recover the actual number of like
                $current_step_like = $step->getStepLike();
                //we subtracted 1 to the number of like
                $new_step_like = $current_step_like - 1;
                //we save the new numbe rof like in step object
                $step->setStepLike($new_step_like);
                // if everything is ok then we save the change in Database
                $manager = $this->getDoctrine()->getManager();
                $manager->flush();
                //we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true
                    ],
                    Response::HTTP_OK
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not connected"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     *  @Route("/comment/{id2}", name="api_step_comment", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function comment(StepRepository $stepRepository, $id2, Request $request, UserRepository $userRepository, JWTEncoderInterface $jWTEncoderInterface, CheckAuthorization $checkAuthorization, Travel $travel)
    {
        //if this step is a part of this travel
        if ($checkAuthorization->checkTravelStep($travel->getId(), $id2, $stepRepository)) {
            //we recover an array with authorizations of the connected user
            $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
            //if the user is connected and follow this step
            if ($userArray["connected"]) {
                //transforms JSON content into Array
                $requestArray = json_decode($request->getContent(), true);
                $user = $userArray["user"];
                //we recover the id of the connected user
                $userId = $user->getId();
                //we put the comment of the request in a variable
                $commentRequest = $requestArray['comment'];
                //we select the desired step object with the url id
                $step = $stepRepository->find($id2);
                //we select the desired user object with the id of the token
                $user = $userRepository->find($userId);
                //we create a new object comment
                $comment = new Comment();
                //we save the step and the user obects in the new comment
                $comment->setStep($step);
                $comment->setUser($user);
                //we save the comment of the request in the new comment
                $comment->setComment($commentRequest);
                //if everything is ok then we save the object in Database
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($comment);
                $manager->flush();
                //we return confirmation message of everything is OK
                return $this->json(
                    [
                        "success" => true,
                        "id" => $comment->getId()

                    ],
                    Response::HTTP_CREATED
                );
            } else {
                //we return an error "HTTP_UNAUTHORIZED"
                return $this->json(
                    [
                        "success" => false,
                        "error" => "you're not connected"
                    ],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } else {
            //we return an error "HTTP_UNAUTHORIZED"
            return $this->json(
                [
                    "success" => false,
                    "error" => "this step is not part of this travel"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     *  @Route("/comment/{id2}/delete", name="api_step_delete_comment", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function deleteComment(UserRepository $userRepository, CommentRepository $commentRepository, $id2, JWTEncoderInterface $jWTEncoderInterface, CheckAuthorization $checkAuthorization, Travel $travel, StepRepository $stepRepository)
    {
        //we recover an array with authorizations of the connected user
        $userArray = $checkAuthorization->checkAuthorization($jWTEncoderInterface, $userRepository, $travel);
        $manager = $this->getDoctrine()->getManager();
        //we select the desired comment object with the url id
        $comment = $commentRepository->find($id2);
        //we recover the Id of the creator of the comment
        $commentCreatorId = $comment->getUser()->getId();
        //if the connected user is the creator of this comment or is an administrator
        if ($userArray["user"]->getId() == $commentCreatorId or $userArray["admin"]) {
            //we remove the comment and save the change in Database
            $manager->remove($comment);
            $manager->flush();
            //we return confirmation message of everything is OK
            return $this->json(
                [
                    "success" => true
                ],
                Response::HTTP_OK
            );
        }
        //else we return an error with "HTTP_UNAUTHORIZED"
        else {
            return $this->json(
                [
                    "success" => false,
                    "error" => "you're not the creator of the comment and you're not an Administrator"
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}
