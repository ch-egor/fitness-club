<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
}
