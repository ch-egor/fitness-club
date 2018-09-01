<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sms")
 */
class FakeSmsController extends Controller
{
    /**
     * @Route("/send", name="sms_send", methods="GET")
     */
    public function send(Request $request, LoggerInterface $logger): Response
    {
        $phone = $request->query->get('phone');
        $message = $request->query->get('message');

        $isSuccess = (rand(0, 4) >= 2);

        if ($isSuccess) {
            $logger->info("Delivered to {$phone}: {$message}");
            return new JsonResponse('Success', 200);
        } else {
            $logger->info("Failed to deliver to {$phone}: {$message}");
            return new JsonResponse('Failure', 500);
        }
    }
}
