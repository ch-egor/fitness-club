<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/", name="client_index", methods="GET")
     */
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', ['clients' => $clientRepository->findAll()]);
    }

    /**
     * @Route("/new", name="client_new", methods="GET|POST")
     */
    public function new(Request $request, \Swift_Mailer $mailer): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->replaceClientPhotoFileWithName($client);
            $client->setEmailConfirmationCode($this->generateEmailConfirmationCode());
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();

            $this->sendEmailConfirmationLetter($mailer, $client);

            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="client_show", methods="GET")
     */
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', ['client' => $client]);
    }

    /**
     * @Route("/{id}/edit", name="client_edit", methods="GET|POST")
     */
    public function edit(Request $request, Client $client): Response
    {
        $this->replaceClientPhotoNameWithFile($client);

        $form = $this->createForm(ClientType::class, $client);
        $form->remove('email');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->replaceClientPhotoFileWithName($client);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('client_edit', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="client_delete", methods="DELETE")
     */
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($client);
            $em->flush();
        }

        return $this->redirectToRoute('client_index');
    }

    /**
     * @Route("/{id}/activate/{code}", name="client_activate", methods="GET|POST")
     */
    public function activate(Request $request, UserPasswordEncoderInterface $passwordEncoder, Client $client, string $code): Response
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

    private function replaceClientPhotoFileWithName(Client $client): void
    {
        // $file stores the uploaded image file
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $client->getPhoto();
        if (!$file) {
            return;
        }

        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        // moves the file to the directory where photos are stored
        $file->move(
            $this->getParameter('photos_directory'),
            $fileName
        );

        // updates the 'photo' property to store the image file name
        // instead of its contents
        $client->setPhoto($fileName);
    }

    private function replaceClientPhotoNameWithFile(Client $client): void
    {
        if (!$client->getPhoto()) {
            return;
        }
        $client->setPhoto(
            new File($this->getParameter('photos_directory') . '/' . $client->getPhoto())
        );
    }
    
    private function generateUniqueFileName(): string
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
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
