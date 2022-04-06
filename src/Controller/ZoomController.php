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
     * @Route("/clientSDK", name="ZoomClientSDKExample")
     */
    public function zoomClientSDK(): Response
    {
        return $this->render('zoom/zoom-client-view.html.twig', [
            'ZOOM_VERSION' => $this->getParameter('app.zoom_version'),
            'ZOOM_API_KEY' => $this->getParameter('app.zoom_api_key'),
        ]);
    }

    /**
     * @Route("/generateSignature", name="generateSignature", methods={"POST"})
     */
    public function generateSignature(Request $request): Response
    {
        dump("here");
        $data = json_decode($request->getContent(), false);
        dump($data);
        dump($request);
        $room = new Room($data->meetingNumber);
        $signature = $room->generateSignature($this->getParameter('app.zoom_api_key'), $this->getParameter('app.zoom_api_secret'), $data->meetingNumber, $data->role);

        return new Response(json_encode($signature), 200);
    }
}
