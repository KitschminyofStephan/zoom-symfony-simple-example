<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Room;

class ZoomController extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     */
    public function index(): Response
    {
        return $this->render('zoom/index.html.twig');
    }

    /**
     * @Route("/clientSDK", name="Zoom client SDK example)
     */
    public function zoomClientSDK(): Response
    {
        return $this->render('zoom/zoom-client-view.html.twig', [
            'ZOOM_API_KEY' => $this->getParameter('app.zoom_api_key'),
        ]);
    }

    /**
     * @Route("/generateSignature", name="generateSignature", methods="{POST}")
     */
    public function generateSignature(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $room = new Room($data["meetingNumber"]);
        $signature = $room->generateSignature($data["apiKey"], $data["apiSecret"], $data["meetingNumber"], $data["role"]);

        return new Response(json_encode($signature), 200);
    }
}
