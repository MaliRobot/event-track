<?php
/**
 * Created by PhpStorm.
 * User: Misha
 * Date: 9/15/2018
 * Time: 3:30 PM
 */

namespace App\Entity;

use App\Entity\Event;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="view_event")
 * @ORM\Entity(repositoryClass="App\Repository\ViewEventRepository")
 */
class ViewEvent extends Event //implements AddOccurrence
{
    const EVENT_TYPE = 'view';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function getEventType()
    {
        return self::EVENT_TYPE;
    }

//    public function eventOccurred()
//    {
//        $this->setNumberOfEvents($this->getNumberOfEvents() + 1);
//    }
}