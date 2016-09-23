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
            ->orderBy('job.step1_downloadedTime', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute(); // ->getOneOrNullResult()
    }

    /**
     * @return Job
     */
    public function findAllPublishedOrderedByRecentlyActive($offset, $limit)
    {

        return $this->createQueryBuilder('job')
            ->andWhere('job.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->leftJoin('job.notes', 'job_note')
            ->orderBy('job.step1_downloadedTime', 'DESC')
            ->orderBy('job_note.createdAt', 'DESC')

            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute(); // ->getOneOrNullResult()
    }

}