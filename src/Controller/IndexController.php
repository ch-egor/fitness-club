<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="index", methods="GET")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('client_index');
    }
}
