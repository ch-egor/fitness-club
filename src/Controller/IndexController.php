<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="index", methods="GET")
     */
    public function index(AuthorizationCheckerInterface $authChecker): Response
    {
        if ($authChecker->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_index');
        }
        if ($authChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('profile_index');
        }
        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/activate/{id}/{code}", name="profile_activate", methods="GET|POST")
     */
    public function activateClient(Request $request, UserPasswordEncoderInterface $passwordEncoder, Client $client, string $code): Response
    {
        if (empty($code) || $client->getEmailConfirmationCode() !== $code) {
            throw $this->createNotFoundException('Wrong confirmation code');
        }

        $form = $this->createForm(ClientPasswordType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($client, $client->getPassword());
            $client->setPassword($password);

            $client->setEmailConfirmationCode(null);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('index/activate.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
}
