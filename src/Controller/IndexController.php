<?php

namespace App\Controller;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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

        $form = $this->createFormBuilder($client)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($client, $client->getPassword());
            $client->setPassword($password);

            $client->setEmailConfirmationCode(null);
            $client->setIsActive(1);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('client/activate.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    private function generateEmailConfirmationCode(): string
    {
        return md5(uniqid());
    }

    private function sendEmailConfirmationLetter(\Swift_Mailer $mailer, Client $client): void
    {
        $message = (new \Swift_Message('Confirm Registration'))
            ->setFrom('noreply@example.com')
            ->setTo($client->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/confirm_registration.html.twig',
                    ['client' => $client]
                ),
                'text/html'
            );

        $mailer->send($message);
    }
}
