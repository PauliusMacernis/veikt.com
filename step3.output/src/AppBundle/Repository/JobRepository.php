<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Job;
use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{

    /**
     * @return Job
     */
    public function findAllPublishedOrderedByDatePosted()
    {

        return $this->createQueryBuilder('job')
            ->andWhere('job.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('job.datePosted', 'DESC')
            ->getQuery()
            ->execute(); // ->getOneOrNullResult()
    }

}