<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\GroupSession;
use App\Form\ClientPasswordType;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
     * @Route("/subscriptions", name="profile_subscriptions", methods="GET|POST")
     */
    public function subscriptions(Request $request, SubscriptionRepository $subscriptionRepository): Response
    {
        $client = $this->getUser();

        $this->generateClientSubscriptions($client);
        $subscriptions = $subscriptionRepository->findByClient($client);

        // TODO: make the form more robust to data change between form load and form submission
        $form = $this->createFormBuilder(['subscriptions' => $subscriptions])
            ->add('subscriptions', CollectionType::class, [
                'label' => false,
                'entry_type' => SubscriptionType::class,
                'entry_options' => ['label' => false],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/subscriptions.html.twig', [
            'form' => $form->createView(),
        ]);
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

    private function generateClientSubscriptions(Client $client): void
    {
        $em = $this->getDoctrine()->getManager();

        $groupSessions = $em->getRepository(GroupSession::class)->findAll();
        foreach ($groupSessions as $groupSession) {
            $groupSession->generateClientSubscription($client);
        }
        
        $em->flush();
    }
}
