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
 * @ORM\Table(name="click_event")
 * @ORM\Entity(repositoryClass="App\Repository\ClickEventRepository")
 */
class ClickEvent extends Event
{
    const EVENT_TYPE = 'click';

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
}