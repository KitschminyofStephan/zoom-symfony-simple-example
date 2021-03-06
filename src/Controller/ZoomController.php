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
     * @Route("/clientView", name="ZoomClientViewExample")
     */
    public function zoomClientView(): Response
    {
        return $this->render('zoom/zoom-client-view.html.twig', [
            'ZOOM_VERSION' => $this->getParameter('app.zoom_version'),
            'ZOOM_SDK_KEY' => $this->getParameter('app.zoom_sdk_key'),
        ]);
    }

    /**
     * @Route("/componentView", name="ZoomComponentViewExample")
     */
    public function zoomComponentView(): Response
    {
        return $this->render('zoom/zoom-component-view.html.twig', [
            'ZOOM_VERSION' => $this->getParameter('app.zoom_version'),
            'ZOOM_SDK_KEY' => $this->getParameter('app.zoom_sdk_key'),
        ]);
    }

    /**
     * @Route("/generateJWTSignature", name="generateJWTSignature", methods={"POST"})
     */
    public function generateJWTSignature(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $room = new Room();
        $room->setMeetingId($data->meetingId);

        $signature = $room->generateJWTSignature($this->getParameter('app.zoom_sdk_key'), $this->getParameter('app.zoom_sdk_secret'), $data->meetingId, $data->role);

        return new Response(json_encode($signature), 200);
    }

    /**
     * @Route("/generateSDKSignature", name="generateSDKSignature", methods={"POST"})
     */
    public function generateSDKSignature(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);
        
        $room = new Room();
        $room->setMeetingId($data->meetingId);

        $signature = $room->generateSDKSignature($this->getParameter('app.zoom_sdk_key'), $this->getParameter('app.zoom_sdk_secret'), $data->meetingId, $data->role);

        return new Response(json_encode(['signature' => $signature]), 200);
    }

    /**
     * @Route("/getMeetingId", name="getMeetingId", methods={"POST"})
     */
    public function getMeetingId(Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $room = new Room();
        $room->setMeetingName($data->meetingName);

        // check if the meet already exist
        $file = $this->getParameter('kernel.project_dir') . "\src\sampleDB.json";

        $meets = json_decode(file_get_contents($file), false);


        foreach ($meets as $meet) {
            if ($meet->meetingName == $room->getMeetingName()) {
                $room->setMeetingId($meet->meetingId);
                $room->setPassword($meet->password);
            }
        }

        // if he doesn't exist, need to create it
        if ($room->getMeetingId() == null) {
            $room = $this->createZoomMeeting($room);

            // Add it to my sample json DB
            $room_array = array("meetingName" => $room->getMeetingName(), "meetingId" => $room->getMeetingId(), "password" => $room->getPassword());
            array_push($meets, $room_array);

            $json = json_encode($meets);
            file_put_contents($file, $json);
        }
        
        return new Response(json_encode(['meetingId' => $room->getMeetingId(), 'password' => $room->getPassword()]), 200);
    }

    private function createZoomMeeting(Room $room) {
        $url = $this->getParameter('app.zoom_api_url') . "/users/me/meetings";

        $token = $room->generateJWTtoken($this->getParameter('app.zoom_api_key'), $this->getParameter('app.zoom_api_secret'));

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
        
        $content = $response->toArray();

        $room->setMeetingId($content["id"]);
        $room->setPassword($content["password"]);

        return $room;
    }
}
