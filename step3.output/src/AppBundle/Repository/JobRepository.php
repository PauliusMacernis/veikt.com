<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Job;
use Doctrine\ORM\EntityRepository;

class JobRepository extends EntityRepository
{

    /**
     * @return Job
     */
    public function findAllPublishedOrderedByDatePosted($offset, $limit)
    {

        return $this->createQueryBuilder('job')
            ->andWhere('job.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('job.datePosted', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute(); // ->getOneOrNullResult()
    }

}