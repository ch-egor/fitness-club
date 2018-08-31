<?php

namespace App\Controller;

use App\Entity\GroupSession;
use App\Form\GroupSessionType;
use App\Repository\GroupSessionRepository;
use App\Service\Notifications;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/session")
 */
class GroupSessionController extends Controller
{
    /**
     * @Route("/", name="group_session_index", methods="GET")
     */
    public function index(GroupSessionRepository $groupSessionRepository): Response
    {
        return $this->render('group_session/index.html.twig', ['group_sessions' => $groupSessionRepository->findAll()]);
    }

    /**
     * @Route("/new", name="group_session_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $groupSession = new GroupSession();
        $form = $this->createForm(GroupSessionType::class, $groupSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupSession);
            $em->flush();

            return $this->redirectToRoute('group_session_index');
        }

        return $this->render('group_session/new.html.twig', [
            'group_session' => $groupSession,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="group_session_show", methods="GET|POST")
     */
    public function show(Request $request, Notifications $notifications, GroupSession $groupSession): Response
    {
        $form = $this->createFormBuilder(['email' => '', 'sms' => ''])
            ->add('email', TextareaType::class, [
                'required' => false,
            ])
            ->add('sms', TextareaType::class, [
                'required' => false,
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messages = $form->getData();
            $notifications->sendGroupSessionEmailNotification($groupSession, $messages['email']);
            $notifications->sendGroupSessionSmsNotification($groupSession, $messages['sms']);

            return $this->redirectToRoute('group_session_index');
        }

        return $this->render('group_session/show.html.twig', [
            'group_session' => $groupSession,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="group_session_edit", methods="GET|POST")
     */
    public function edit(Request $request, GroupSession $groupSession): Response
    {
        $form = $this->createForm(GroupSessionType::class, $groupSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('group_session_edit', ['id' => $groupSession->getId()]);
        }

        return $this->render('group_session/edit.html.twig', [
            'group_session' => $groupSession,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="group_session_delete", methods="DELETE")
     */
    public function delete(Request $request, GroupSession $groupSession): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groupSession->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupSession);
            $em->flush();
        }

        return $this->redirectToRoute('group_session_index');
    }
}
