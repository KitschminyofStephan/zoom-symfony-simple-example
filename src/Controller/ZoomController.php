<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ZoomController extends AbstractController
{
    /**
     * @Route("/", name="Zoom")
     */
    public function index(): Response
    {
        return $this->render('zoom/index.html.twig');
    }
}
