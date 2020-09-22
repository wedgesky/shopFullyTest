<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{


    /**
     * @Route("/ciao", name="ciao")
     */
    public function prova()
    {
        return new JsonResponse("ciao");
    }
}
