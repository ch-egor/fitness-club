<?php

namespace App\Controller;

use App\Form\ClientPasswordType;
use App\Repository\GroupSessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="profile_index", methods="GET")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = $this->getUser();

        return $this->render('profile/index.html.twig', ['client' => $client]);
    }

    /**
     * @Route("/sessions", name="profile_group_sessions", methods="GET")
     */
    public function groupSessions(GroupSessionRepository $groupSessionRepository): Response
    {
        return $this->render('profile/group_sessions.html.twig', ['group_sessions' => $groupSessionRepository->findAll()]);
    }

    /**
     * @Route("/change-password", name="profile_change_password", methods="GET|POST")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = $this->getUser();
        $form = $this->createForm(ClientPasswordType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($client, $client->getPassword());
            $client->setPassword($password);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/change_password.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
}
