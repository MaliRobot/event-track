<?php
/**
 * Created by PhpStorm.
 * User: Misha
 * Date: 9/16/2018
 * Time: 11:57 PM
 */

namespace App\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Entity\ClickEvent;
use App\Entity\ViewEvent;
use App\Entity\PlayEvent;
use Doctrine\ORM\EntityManagerInterface;

class ReceiveDataService implements ConsumerInterface
{
    protected $em;

    public function __construct(EntityManagerInterface  $entityManager) {
        $this->em = $entityManager;
    }

    public function execute(AMQPMessage $msg)
    {
        $response = json_decode($msg->body, true);
        $type = $response["type"];
        $date = $response["date"];
        $countryCode = $response["country_code"];
        $this->makeEntry($type, $date, $countryCode);
    }

    private function makeEntry($type, $date, $countryCode){
        $date = date_create_from_format('Y-m-d', substr($date["date"], 0, 10));
        if($type == 'click'){
            $repository = $this->em->getRepository(ClickEvent::class);
            $eventArray = $repository->findBy(['date' => $date, 'countryCode' => $countryCode]);
            if (!$eventArray){
                $event = new ClickEvent();
                $event->setCountryCode($countryCode);
                $event->setDate($date);
            } else {
                $event = $eventArray[0];
            }
        } elseif ($type == 'view') {
            $repository = $this->em->getRepository(ViewEvent::class);
            $eventArray = $repository->findBy(['date' => $date, 'countryCode' => $countryCode]);
            if (!$eventArray){
                $event = new ViewEvent();
                $event->setCountryCode($countryCode);
                $event->setDate($date);
            } else {
                $event = $eventArray[0];
            }
        } else {
            $repository = $this->em->getRepository(PlayEvent::class);
            $eventArray = $repository->findBy(['date' => $date, 'countryCode' => $countryCode]);
            if (!$eventArray){
                $event = new PlayEvent();
                $event->setCountryCode($countryCode);
                $event->setDate($date);
            } else {
                $event = $eventArray[0];
            }
        }

        $event->eventOccurred();

        $this->em->persist($event);
        $this->em->flush();
    }
}