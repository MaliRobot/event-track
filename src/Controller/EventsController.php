<?php
/**
 * Created by PhpStorm.
 * User: Misha
 * Date: 9/15/2018
 * Time: 5:45 PM
 */

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

class EventsController extends FOSRestController {
    /**
     * @Rest\Post("/api/add_event")
     */
    public function addEventAction(Request $request): Response
    {
        // validate type
        $type = $request->request->get('type');
        if(!in_array($type, ['click', 'view', 'play'])){
            return new Response('Wrong event type!', Response::HTTP_BAD_REQUEST);
        }

        // validate date
        $date = $request->request->get('date');
        if(!$date) {
            return new Response('Missing time value!', Response::HTTP_BAD_REQUEST);
        } else {
            try {
                $date = date_create_from_format('Y-m-d', $date);
            } catch (\Exception $e) {
                return new Response('Wrong time format!', Response::HTTP_BAD_REQUEST);
            }
        }

        // validate country code
        $countryCode = $request->request->get('region');
        $regionNames = json_decode(file_get_contents("http://country.io/names.json"), true);
        if(!in_array($countryCode, array_keys($regionNames))){
            return new Response('Not a valid country code!', Response::HTTP_BAD_REQUEST);
        }

        $this->get('old_sound_rabbit_mq.receive_data_producer')->setContentType('application/json');
        $this->get('old_sound_rabbit_mq.receive_data_producer')->publish(json_encode(["type" => $type, "date" => $date, "country_code" => $countryCode]));

        return new Response('Event of ' . $type . ' in country ' . $countryCode . " received!", Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get("/api/get_events")
     */
    public function getEventsAction(Request $request)
    {
        $format = $request->query->get('format');
        if (!in_array($format, ['csv', 'json'])){
            return new Response('Bad file format requested!', Response::HTTP_BAD_REQUEST);
        }

        if($format == "json"){
            $filename = (string) bin2hex(random_bytes(16)) . '.json';
            $link = $_SERVER['SERVER_NAME'] . "/api/downloads/" . $filename;
        } else {
            $filename = (string) bin2hex(random_bytes(16)) . '.csv';
            $link = $_SERVER['SERVER_NAME'] . "/api/downloads/" .  $filename;
        }

        $this->get('old_sound_rabbit_mq.send_data_producer')->setContentType('application/json');
        $this->get('old_sound_rabbit_mq.send_data_producer')->publish(json_encode(["format" => $format, "filename" => $filename]));

        return new Response('Data file in ' . $format . " requested! Download link will be available here (please wait while we generate it, it may take a while): " . $link, Response::HTTP_OK , []);
    }

    /**
     * @Rest\Get("/api/downloads/{filename}")
     */
    public function downloadAction($filename)
    {
        $file = $this->get('kernel')->getRootDir() . "\..\public\downloads\/". $filename;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    }
}

