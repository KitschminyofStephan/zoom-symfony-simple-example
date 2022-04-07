<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Model\Room;

class ZoomController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $http)
    {
        $this->httpClient = $http;
    }

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
            'ZOOM_SDK_KEY' => $this->getParameter('app.zoom_sdk_key'),
            'meetingNumber' => '123456789',
        ]);
    }

    /**
     * @Route("/generateJWTSignature", name="generateJWTSignature", methods={"POST"})
     */
    public function generateJWTSignature(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $room = new Room();
        $room->setMeetingNumber($data->meetingNumber);

        $signature = $room->generateJWTSignature($this->getParameter('app.zoom_sdk_key'), $this->getParameter('app.zoom_sdk_secret'), $data->meetingNumber, $data->role);

        return new Response(json_encode($signature), 200);
    }

    /**
     * @Route("/generateSDKSignature", name="generateSDKSignature", methods={"POST"})
     */
    public function generateSDKSignature(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);
        
        $room = new Room();
        $room->setMeetingNumber($data->meetingNumber);

        $signature = $room->generateSDKSignature($this->getParameter('app.zoom_sdk_key'), $this->getParameter('app.zoom_sdk_secret'), $data->meetingNumber, $data->role);

        return new Response(json_encode($signature), 200);
    }

    /**
     * @Route("/getMeetingNumber", name="getMeetingNumber", methods={"POST"})
     */
    public function getMeetingNumber(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $room = new Room();
        $room->setMeetingName($data->meetingName);

        // check if the meet already exist
        $path = $this->getParameter('kernel.project_dir') . "\src\sampleDB.json";

        $jsonDB = json_decode(file_get_contents($path), false);
        $meetsDB = $jsonDB->meets;


        foreach ($meetsDB as $meetDB) {
            if ($meetDB->meetingName == $room->getMeetingName()) {
                $room->setMeetingNumber($meetDB->meetingNumber);
            }
        }

        // if he doesn't exist, need to create it
        if ($room->getMeetingNumber() == null) {
            $response = $this->createZoomMeeting($room);
            dump($response);
        }
    

        // $room->getMeetingNumber();
        
        return new Response("In progress", 500);
    }

    private function createZoomMeeting(Room $room) {
        $url = $this->getParameter('app.zoom_api_url') . "/users/stephank51@gmail.com/meetings";

        dump($url);

        $token = $room->generateJWTtoken($this->getParameter('app.zoom_api_key'), $this->getParameter('app.zoom_api_secret'));
        dump($token);

        $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'User-Agent' => 'Zoom-api-Jwt-Request',
                    'Content-Type' => 'application/json'
                ],
                'auth_bearer' => $token,
                'json' => [
                    'default_password' => false
                ],
            ]
        );
        // TODO Erreur 400 bad request GET to POST
        // To debug try sample request get user info and next try create meeting

        $statusCode = $response->getStatusCode();
        dump($statusCode);

        $content = $response->getContent();
        dump($content);

        return $response;
    }
}
