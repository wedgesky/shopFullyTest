<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFlyerController extends AbstractController
{
     /**
     * @Route("/flyers")
     */
    public abstract function index(Request $request);

    /**
     * @Route("/flyers/{id}", requirements={"id"="\d+"})
     */
    public abstract function showId($id, Request $request);
}