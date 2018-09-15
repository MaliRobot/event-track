<?php
/**
 * Created by PhpStorm.
 * User: Misha
 * Date: 9/15/2018
 * Time: 3:02 PM
 */

namespace App\Entity;

use Doctrine\Orm\Mapping as ORM;
use Doctrine\Orm\Mapping\Column;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Event
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    protected $numberOfEvents;

    /**
     * @Assert\Date()
     *
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $countryCode;

    abstract protected function getEventType();

//    /**
//     * @return mixed
//     */
//    public function getCountryCode()
//    {
//        return $this->countryCode;
//    }
//
//    /**
//     * @param mixed $countryCode
//     */
//    public function setCountryCode($countryCode): void
//    {
//        $this->countryCode = $countryCode;
//    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getNumberOfEvents()
    {
        return $this->numberOfEvents;
    }

    /**
     * @param mixed $numberOfEvents
     */
    public function setNumberOfEvents($numberOfEvents): void
    {
        $this->numberOfEvents = $numberOfEvents;
    }

}