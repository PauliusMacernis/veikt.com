<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Job;
use AppBundle\Form\JobSearchFormTypeData;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
    public function findAllPublishedOrderedByRecentlyActive(JobSearchFormTypeData $data, $currentPage = 1, $limit = 5 /*$offset, $limit*/)
    {

        $query = $this->findAllPublishedOrderedByRecentlyActiveQ($data);

        // Paginator
        $paginator = new Paginator($query);
        $rowsCountTotal = $paginator->count();

        $data = $query
            ->setFirstResult($limit * ($currentPage - 1)) // Offset
            ->setMaxResults($limit) // Limit
            ->execute(); // ->getOneOrNullResult()
        $rowsCountThisPage = count($data);

        return [
            'limit' => $limit,
            'currentPage' => $currentPage,
            'maxPages' => ceil($rowsCountTotal / $limit),
            'rowsCountTotal' => $rowsCountTotal,
            'rowsCountThisPage' => $rowsCountThisPage,
            'data' => $data,
        ];

    }

    private function findAllPublishedOrderedByRecentlyActiveQ(JobSearchFormTypeData $data) {
        return
            $this->createQueryBuilder('job')
            ->andWhere('job.isPublished = :isPublished')
            ->setParameter('isPublished', true)

            ->andWhere('job.step1_html LIKE :step1_html')
            ->setParameter('step1_html', '%' . $data->getStep1Html() . '%')

            ->leftJoin('job.notes', 'job_note')
            ->orderBy('job.step1_downloadedTime', 'DESC')
            //->orderBy('job_note.createdAt', 'DESC')

            //->setFirstResult($limit * ($currentPage - 1)) // Offset
            //->setMaxResults($limit) // Limit
            ->getQuery();
    }

    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    /*public function paginate($paginator, $page = 1, $limit = 5)
    {
        //$paginator = new Paginator($dql);

        $paginator
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }*/

}