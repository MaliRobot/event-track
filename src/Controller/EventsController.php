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

class EventsController extends FOSRestController {
    /**
     * @Rest\Post("/api/add_event")
     */
    public function addEventAction(Request $request): Response
    {
        dump($request);
        return new Response('add events hit', Response::HTTP_CREATED , []);
    }

    /**
     * @Rest\Get("/api/get_events")
     */
    public function getEventsAction(Request $request): Response
    {
        dump($request);
        return new Response('get events hit', Response::HTTP_CREATED , []);
    }
}

