<?php
/**
 * Created by PhpStorm.
 * User: Misha
 * Date: 9/15/2018
 * Time: 9:13 PM
 */

namespace App\Repository;

use App\Entity\ViewEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ViewEventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ViewEvent::class);
    }

    /**
     * @param int $countries
     * @return mixed
     */
    public function mostEventsByCountry($countries = 5){
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT SUM(c.numberOfEvents), c.countryCode 
            FROM App\Entity\ViewEvent c 
            WHERE c.date > :date
            GROUP BY c.countryCode'
        )->setParameter('date', new \DateTime('-7 days'))
        ->setMaxResults($countries);

        return $query->execute();
    }
}