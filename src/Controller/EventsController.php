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
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\ClickEvent;
use App\Entity\ViewEvent;
use App\Entity\PlayEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

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

        $allData = ['clicks' => [], 'views' => [], 'plays' => []];
        $allData = $this->addClicks($allData);
        $allData = $this->addViews($allData);
        $allData = $this->addPlays($allData);

        if($format == 'json'){
            $this->fetchJSON($allData);
        } else {
            $this->fetchCSV($allData);
        }

    }

    private function addClicks($allData) {
        $clickRepository = $this->getDoctrine()->getRepository(ClickEvent::class);
        $clicks = $clickRepository->mostEventsByCountry();
        foreach($clicks as $click){
            $allData['clicks'][$click['countryCode']] = $click[1];
        }
        return $allData;
    }

    private function addViews($allData){
        $viewRepository = $this->getDoctrine()->getRepository(ViewEvent::class);
        $views = $viewRepository->mostEventsByCountry();
        foreach($views as $view){
            $allData['views'][$view['countryCode']] = $view[1];
        }
        return $allData;
    }

    private function addPlays($allData){
        $playRepository = $this->getDoctrine()->getRepository(PlayEvent::class);
        $plays = $playRepository->mostEventsByCountry();
        foreach($plays as $play){
            $allData['plays'][$play['countryCode']] = $play[1];
        }
        return $allData;
    }

    private function fetchJSON($allData){
        $filename = 'event_data.json';
        $fp = fopen($filename, 'w');

        fwrite($fp, json_encode($allData));
        fclose($fp);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        unlink($filename);
        exit;
    }

    private function fetchCSV($allData){
        $filename = 'event_data.csv';
        $fp = fopen($filename, 'w');

        foreach ($allData as $key => $value) {
            fputcsv($fp, [$key, ''], ',');
            foreach($value as $k => $v){
                fputcsv($fp, [$k, $v], ',');
            }
        }
        fclose($fp);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize('event_data.csv'));
        readfile($filename);
        unlink($filename);
        exit;
    }
}

