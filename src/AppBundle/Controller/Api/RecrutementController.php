<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Ad;
use AppBundle\Entity\Category;
use AppBundle\Entity\Recrutement;
use AppBundle\Entity\RecrutementUser;
use AppBundle\Entity\User;
use Backend\UserBundle\Entity\newsletter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\Validate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class RecrutementController extends Controller
{

    /**
     * @ApiDoc(
     *      resource=true,
     *     description="Get one single recrutement",
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirements"="\d+",
     *             "description"="The recrutement unique identifier."
     *         }
     *     },
     *     section="recrutements"
     * )
     * @Route("/api/admin/recrutements/{id}",name="show_recrutement")
     * @Method({"GET"})
     */
    public function showRecrutement($id)
    {
        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($id);


        if (empty($recrutement)) {
            $response = array(
                'code' => 1,
                'message' => 'recrutement not found',
                'error' => null,
                'result' => null
            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $this->get('jms_serializer')->serialize($recrutement, 'json');


        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }


    /**
     * @ApiDoc(
     * description="Create a new recrutement",
     *
     *    statusCodes = {
     *        201 = "Creation with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=Recrutement::class},
     *
     *    },
     *     section="recrutements"
     *
     *
     * )
     *
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/recrutements",name="create_recrutement")
     * @Method({"POST"})
     */
    public function createRecrutement(Request $request, Validate $validate)
    {


        $recrutement = new Recrutement();
        $cv = $request->files->get('cv');
        $email = $request->get('email');
        $genre = $request->get('genre');
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $pays = $request->get('pays');
        $ville = $request->get('ville');
        $adresse = $request->get('adresse');
        $code_postal = $request->get('code_postal');
        $sujet = $request->get('sujet');
        $motivation = $request->get('motivation');
        $telephone = $request->get('telephone');
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $cv;
        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        $file->move(
            $this->getParameter('brochures_directory'),
            $fileName
        );

        $poste = $this->getDoctrine()->getRepository('AppBundle:Ad')->find($sujet);
        $recrutement->setCv($fileName);
        $recrutement->setEmail($email);
        $recrutement->setGenre($genre);
        $recrutement->setNom($nom);
        $recrutement->setPrenom($prenom);
        $recrutement->setPays($pays);
        $recrutement->setVille($ville);
        $recrutement->setAdresse($adresse);
        $recrutement->setCodePostal($code_postal);
        $recrutement->setAd($poste);
        $recrutement->setMotivation($motivation);
        $recrutement->setTelephone($telephone);
        $recrutement->setEtat(false);
        $recrutement->setApproved(null);

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($recrutement);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutement);
        $em->flush();


        $response = array(

            'code' => 0,
            'message' => 'Recrutement created!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, Response::HTTP_CREATED);


    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of all recrutements",
     *     section="recrutements"
     * )
     *
     * @Route("/api/admin/recrutements",name="list_recrutements")
     * @Method({"GET"})
     */

    public function listRecrutement()
    {

        $recrutements = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->findAll();


        $data = $this->get('jms_serializer')->serialize($recrutements, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }


    /**
     * @param Request $request
     * @param $id
     * @Route("/api/admin/recrutements/{id}",name="update_recrutement")
     * @Method({"PUT"})
     * @return JsonResponse
     */
    public function updateRecrutement(Request $request, $id, Validate $validate)
    {

        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($id);

        if (empty($recrutement)) {
            $response = array(

                'code' => 1,
                'message' => 'Recrutement Not found !',
                'errors' => null,
                'result' => null

            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $body = $request->getContent();


        $data = $this->get('jms_serializer')->deserialize($body, 'AppBundle\Entity\Recrutement', 'json');


        $reponse = $validate->validateRequest($data);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);

        }

        $recrutement->setTitle($data->getTitle());
        $recrutement->setDescription($data->getDescription());

        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutement);
        $em->flush();

        $response = array(

            'code' => 0,
            'message' => 'Recrutement updated!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, 200);

    }

    /**
     * @Route("/api/recrutements/delete/{id}",name="delete_recrutement")
     * @Method({"DELETE"})
     */

    public function deleteRecrutement($id)
    {
        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($id);

        if (empty($recrutement)) {

            $response = array(

                'code' => 1,
                'message' => 'recrutement Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($recrutement);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'recrutement deleted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/admin/recrutements/accept/{id}",name="accept_recrutement")
     * @Method({"GET"})
     */

    public function acceptRecrutement($id)
    {
        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($id);

        if (empty($recrutement)) {

            $response = array(

                'code' => 1,
                'message' => 'recrutement Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $recrutement->setApproved(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutement);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'recrutement accepted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/admin/recrutements/refuse/{id}",name="refuse_recrutement")
     * @Method({"GET"})
     */

    public function refuseRecrutement($id)
    {
        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($id);

        if (empty($recrutement)) {

            $response = array(

                'code' => 1,
                'message' => 'recrutement Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $recrutement->setApproved(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutement);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'recrutement refused !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

//    categories

    /**
     * @ApiDoc(
     * description="Create a new Category",
     *
     *    statusCodes = {
     *        201 = "Creation with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=Category::class},
     *
     *    },
     *     section="Category"
     *
     *
     * )
     *
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/admin/category",name="create_category")
     * @Method({"POST"})
     */
    public function createCategory(Request $request, Validate $validate)
    {

        $ad = new Category();
        $label = $request->get('label');
        $description = $request->get('description');
        $image = $request->files->get('image');
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $image;
        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        $file->move(
            $this->getParameter('brochures_directory'),
            $fileName
        );
        $ad->setLabel($label);
        $ad->setDescription($description);
        $ad->setImage($fileName);


        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($ad);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($ad);
        $em->flush();


        $response = array(

            'code' => 0,
            'message' => 'Category created!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, Response::HTTP_CREATED);

    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of all Category",
     *     section="Jobs"
     * )
     *
     * @Route("/api/category",name="list_category")
     * @Method({"GET"})
     */

    public function listCategory()
    {

        $ads = $this->getDoctrine()->getRepository('AppBundle:Category')->findAll();
        if (!count($ads)) {
            $response = array(

                'code' => 1,
                'message' => 'No Category found!',
                'errors' => null,
                'result' => json_decode('[]')

            );


            return new JsonResponse($response, Response::HTTP_OK);
        }


        $data = $this->get('jms_serializer')->serialize($ads, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }


    /**
     * @param Request $request
     * @param $id
     * @Route("/api/admin/category/{id}",name="update_category")
     * @Method({"PUT"})
     * @return JsonResponse
     */
    public function updateCategory(Request $request, $id, Validate $validate)
    {

        $ad = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);

        if (empty($ad)) {
            $response = array(

                'code' => 1,
                'message' => 'Category Not found !',
                'errors' => null,
                'result' => null

            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $body = $request->getContent();


        $data = $this->get('jms_serializer')->deserialize($body, 'AppBundle\Entity\Category', 'json');


        $reponse = $validate->validateRequest($data);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);

        }

        $ad->setTitle($data->getTitle());
        $ad->setDescription($data->getDescription());

        $em = $this->getDoctrine()->getManager();
        $em->persist($ad);
        $em->flush();

        $response = array(

            'code' => 0,
            'message' => 'Category updated!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, 200);

    }

    /**
     * @Route("/api/admin/category/delete/{id}",name="delete_category")
     * @Method({"DELETE"})
     */

    public function deleteCategory($id)
    {
        $ad = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);

        if (empty($ad)) {

            $response = array(

                'code' => 1,
                'message' => 'Category Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($ad);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'Category deleted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

//    end categories

    /**
     * @ApiDoc(
     * description="Create a new Job",
     *
     *    statusCodes = {
     *        201 = "Creation with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=Ad::class},
     *
     *    },
     *     section="Jobs"
     *
     *
     * )
     *
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/admin/jobs",name="create_job")
     * @Method({"POST"})
     */
    public function createAd(Request $request, Validate $validate)
    {

        $ad = new Ad();
        $label = $request->get('label');
        $entreprise = $request->get('entreprise');
        $adresse = $request->get('adresse');
        $description = $request->get('description');
        $category = $request->get('category');
        $image = $request->files->get('image');

        $cat = $this->getDoctrine()->getRepository('AppBundle:Category')->find($category);

        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $image;
        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        $file->move(
            $this->getParameter('brochures_directory'),
            $fileName
        );
        $ad->setLabel($label);
        $ad->setEntreprise($entreprise);
        $ad->setAdresse($adresse);
        $ad->setDescription($description);
        $ad->setCategory($cat);
        $ad->setImage($fileName);
        $ad->setDate(new \DateTime());


        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($ad);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($ad);
        $em->flush();


        $response = array(

            'code' => 0,
            'message' => 'Job created!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, Response::HTTP_CREATED);

    }


    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of all Jobs",
     *     section="Jobs"
     * )
     *
     * @Route("/api/jobs",name="list_jobs")
     * @Method({"GET"})
     */

    public function listAd()
    {

        $ads = $this->getDoctrine()->getRepository('AppBundle:Ad')->findAll();
        if (!count($ads)) {
            $response = array(

                'code' => 1,
                'message' => 'No jobs found!',
                'errors' => null,
                'result' => json_decode('[]')

            );


            return new JsonResponse($response, Response::HTTP_OK);
        }


        $data = $this->get('jms_serializer')->serialize($ads, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }


    /**
     * @param Request $request
     * @param $id
     * @Route("/api/admin/jobs/{id}",name="update_job")
     * @Method({"PUT"})
     * @return JsonResponse
     */
    public function updateAd(Request $request, $id, Validate $validate)
    {

        $ad = $this->getDoctrine()->getRepository('AppBundle:Ad')->find($id);

        if (empty($ad)) {
            $response = array(

                'code' => 1,
                'message' => 'Ad Not found !',
                'errors' => null,
                'result' => null

            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $body = $request->getContent();


        $data = $this->get('jms_serializer')->deserialize($body, 'AppBundle\Entity\Ad', 'json');


        $reponse = $validate->validateRequest($data);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);

        }

        $ad->setTitle($data->getTitle());
        $ad->setDescription($data->getDescription());

        $em = $this->getDoctrine()->getManager();
        $em->persist($ad);
        $em->flush();

        $response = array(

            'code' => 0,
            'message' => 'Ad updated!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, 200);

    }

    /**
     * @Route("/api/admin/jobs/delete/{id}",name="delete_job")
     * @Method({"DELETE"})
     */

    public function deleteAd($id)
    {
        $ad = $this->getDoctrine()->getRepository('AppBundle:Ad')->find($id);

        if (empty($ad)) {

            $response = array(

                'code' => 1,
                'message' => 'Ad Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($ad);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'Ad deleted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @ApiDoc(
     * description="Create a new Newsletter",
     *
     *    statusCodes = {
     *        201 = "Creation with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=Newsletter::class},
     *
     *    },
     *     section="Newsletters"
     *
     *
     * )
     *
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/newsletter",name="create_newsletter")
     * @Method({"POST"})
     */
    public function createNewsletter(Request $request, Validate $validate, \Swift_Mailer $mailer)
    {

        $newsletter = new \AppBundle\Entity\newsletter();
        $email = $request->get('email');
        var_dump($email);
        $newsletter->setEmail($email);
        $em = $this->getDoctrine()->getManager();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($newsletter);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $test = $em->getRepository('AppBundle:newsletter')
            ->findOneBy(array("email" => $email));

        if ($test != null) {
            $found = array(

                'code' => 1,
                'message' => 'Vous êtes déjà inscrit!',
                'errors' => null,
                'result' => null

            );
            return new JsonResponse($found, Response::HTTP_FOUND);
        } else {
            $message = (new \Swift_Message('Inscription newsletter'))
                ->setFrom(['cheblijassem@gmail.com' => 'NOVUSVIA'])
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        'Emails/newsletter.html.twig',
                        ['email' => $email]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newsletter);
            $em->flush();


            $response = array(

                'code' => 0,
                'message' => 'newsletter created!',
                'errors' => null,
                'result' => null

            );

            return new JsonResponse($response, Response::HTTP_CREATED);
        }


    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


    /**
     * @ApiDoc(
     * description="Create a new RecrutementUser",
     *
     *    statusCodes = {
     *        201 = "Creation with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=RecrutementUser::class},
     *
     *    },
     *     section="RecrutementUser"
     *
     *
     * )
     *
     * @param $Ad_id
     * @param $User_id
     * @return JsonResponse
     * @Route("/api/recrutements/interested/{Ad_id}/{username}",name="interested")
     * @Method({"POST"})
     */
    public function interested($Ad_id, $username, Validate $validate)
    {
        $interest = new RecrutementUser();
        $ad = $this->getDoctrine()->getRepository('AppBundle:Ad')->find($Ad_id);
        $client = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(["username"=>$username]);
        $interest->setAd($ad);
        $interest->setUser($client);
        $interest->setStatus("en attente");


        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($interest);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($interest);
        $em->flush();


        $response = array(

            'code' => 0,
            'message' => 'interest created!',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, Response::HTTP_CREATED);

    }

    /**
     * @Route("/api/recrutements/ignored/{Ad_id}/{username}",name="ignored")
     * @Method({"DELETE"})
     */

    public function deleteInterest($Ad_id, $username)
    {
        $ad = $this->getDoctrine()->getRepository('AppBundle:Ad')->find($Ad_id);
        $client = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(["username"=>$username]);
        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->findOneBy(["ad"=>$ad,"user"=>$client]);

        if (empty($recrutementUser)) {

            $response = array(

                'code' => 1,
                'message' => 'RecrutementUser Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($recrutementUser);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'RecrutementUser deleted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/admin/recrutements/demande/{id}",name="demande_delete")
     * @Method({"DELETE"})
     */

    public function demande_delete($id)
    {
        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->find($id);

        if (empty($recrutementUser)) {

            $response = array(

                'code' => 1,
                'message' => 'RecrutementUser Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($recrutementUser);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'RecrutementUser deleted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }


    /**
     * @Route("/api/recrutements/accept/{id}",name="accept")
     * @Method({"GET"})
     */

    public function acceptInterest($id)
    {

        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->find($id);

        if (empty($recrutementUser)) {

            $response = array(

                'code' => 1,
                'message' => 'RecrutementUser Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $recrutementUser->setStatus("accepted");
        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutementUser);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'RecrutementUser accepted !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/recrutements/refuse/{id}",name="refuse")
     * @Method({"GET"})
     */

    public function refuseInterest($id)
    {

        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->find($id);

        if (empty($recrutementUser)) {

            $response = array(

                'code' => 1,
                'message' => 'RecrutementUser Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $recrutementUser->setStatus("refused");
        $em = $this->getDoctrine()->getManager();
        $em->persist($recrutementUser);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'RecrutementUser refused !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }
    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of followed profiles",
     *     section="recrutements"
     * )
     *
     * @Route("/api/admin/followed/{username}",name="followed")
     * @Method({"GET"})
     */

    public function followed($username)
    {
        $ad =[];
        $client = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(["username"=>$username]);
        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->findBy(["user"=>$client]);
        foreach($recrutementUser as $r)
        {
            array_push($ad,$r->getAd());
        }

        $data = $this->get('jms_serializer')->serialize($ad, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }
    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of accepted profiles",
     *     section="recrutements"
     * )
     *
     * @Route("/api/admin/accepted/{username}",name="accepted")
     * @Method({"GET"})
     */

    public function accepted($username)
    {
        $ad =[];
        $client = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(["username"=>$username]);
        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->findBy(["status"=>'accepté',"user"=>$client]);
        foreach($recrutementUser as $r)
        {
            array_push($ad,$r->getAd());
        }

        $data = $this->get('jms_serializer')->serialize($ad, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }



    /************************/

    /**
     * @ApiDoc(
     * description="Register user",
     *
     *    statusCodes = {
     *        201 = "Registration with success",
     *        400 = "invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=User::class},
     *
     *    },
     *     section="Users"
     *
     *
     * )
     *
     * @param Request $request
     * @param Validate $validate
     * @return JsonResponse
     * @Route("/api/register",name="register")
     * @Method({"POST"})
     */

    public function Register(Request $request, Validate $validate, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();
        $email = $request->get('email');
        $username = $request->get('username');
        $plainPassword = $request->get('password');

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $plainPassword
            )
        );



        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize($user);
        $reponse = $validate->validateRequest($formatted);

        if (!empty($reponse)) {
            return new JsonResponse($reponse, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();


        $response = array(

            'code' => 0,
            'message' => 'User Registred',
            'errors' => null,
            'result' => null

        );

        return new JsonResponse($response, Response::HTTP_CREATED);

    }
    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of demandes",
     *     section="RecrutementUser"
     * )
     *
     * @Route("/api/admin/demandes",name="demandes")
     * @Method({"GET"})
     */

    public function demandes()
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->findAll();
        $data = $this->get('jms_serializer')->serialize($users, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of users",
     *     section="users"
     * )
     *
     * @Route("/api/admin/users",name="users")
     * @Method({"GET"})
     */

    public function users()
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $data = $this->get('jms_serializer')->serialize($users, 'json');

        $response = array(

            'code' => 0,
            'message' => 'success',
            'errors' => null,
            'result' => json_decode($data)

        );

        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/admin/users/enable/{id}",name="enable")
     * @Method({"GET"})
     */

    public function enable($id)
    {

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if (empty($user)) {

            $response = array(

                'code' => 1,
                'message' => 'User Not found !',
                'errors' => null,
                'result' => null

            );

            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $user->setEnabled(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'User enabled !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }

    /**
     * @Route("/api/recrutements/affecter/{id}/{recrutement}",name="affecter")
     * @Method({"GET"})
     */

    public function affecter($id, $recrutement)
    {

        $recrutementUser = $this->getDoctrine()->getRepository('AppBundle:RecrutementUser')->find($id);
        $recrutement = $this->getDoctrine()->getRepository('AppBundle:Recrutement')->find($recrutement);

        if (empty($recrutementUser)) {

            $response = array(

                'code' => 1,
                'message' => 'RecrutementUser Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        if (empty($recrutement)) {

            $response = array(

                'code' => 1,
                'message' => 'Recrutement Not found !',
                'errors' => null,
                'result' => null

            );


            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $recrutement->setEtat("affecté");
        $recrutementUser->setStatus("accepté");
        $recrutementUser->setRecrutement($recrutement);
        $em = $this->getDoctrine()->getManager();

        $em->persist($recrutement);
        $em->persist($recrutementUser);
        $em->flush();
        $response = array(

            'code' => 0,
            'message' => 'Recrutement affected !',
            'errors' => null,
            'result' => null

        );


        return new JsonResponse($response, 200);


    }
}
