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
use Symfony\Component\HttpFoundation\Response;

class SendDataService implements ConsumerInterface
{
    protected $em;

    public function __construct(EntityManagerInterface  $entityManager) {
        $this->em = $entityManager;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $response = json_decode($msg->body, true);

        $allData = ['clicks' => [], 'views' => [], 'plays' => []];
        $allData = $this->addClicks($allData);
        $allData = $this->addViews($allData);
        $allData = $this->addPlays($allData);

        $format = $response["format"];
        $filename = "public/downloads/" . $response["filename"];
        if($format == 'json'){
            $this->fetchJSON($allData, $filename);
        } else {
            $this->fetchCSV($allData, $filename);
        }
    }

    /**
     * @param $allData
     * @return mixed
     */
    private function addClicks($allData) {
        $clickRepository = $this->em->getRepository(ClickEvent::class);
        $clicks = $clickRepository->mostEventsByCountry();

        foreach($clicks as $click){
            $allData['clicks'][$click['countryCode']] = $click[1];
        }
        return $allData;
    }

    /**
     * @param $allData
     * @return mixed
     */
    private function addViews($allData){
        $viewRepository = $this->em->getRepository(ViewEvent::class);
        $views = $viewRepository->mostEventsByCountry();
        foreach($views as $view){
            $allData['views'][$view['countryCode']] = $view[1];
        }
        return $allData;
    }

    /**
     * @param $allData
     * @return mixed
     */
    private function addPlays($allData){
        $playRepository = $this->em->getRepository(PlayEvent::class);
        $plays = $playRepository->mostEventsByCountry();
        foreach($plays as $play){
            $allData['plays'][$play['countryCode']] = $play[1];
        }
        return $allData;
    }

    /**
     * @param $allData
     * @param $filename
     */
    private function fetchJSON($allData, $filename){
        $fp = fopen($filename, 'w');

        fwrite($fp, json_encode($allData));
        fclose($fp);
    }

    /**
     * @param $allData
     * @param $filename
     */
    private function fetchCSV($allData, $filename) {
        $fp = fopen($filename, 'w');

        foreach ($allData as $key => $value) {
            fputcsv($fp, [$key, ''], ',');
            foreach ($value as $k => $v) {
                fputcsv($fp, [$k, $v], ',');
            }
        }
        fclose($fp);
    }
}