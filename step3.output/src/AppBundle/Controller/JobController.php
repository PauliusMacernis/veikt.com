<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-07-29
 * Time: 22:27
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Job;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class JobController extends Controller
{
    /**
     * @Route("/job/new")
     */
    public function newAction() {
        $job = new Job();
        $job->setStep1Html('Test' . rand(1,100));
        $job->setStep1Id('S' . rand(1,100000));
        $job->setStep1Statistics('Stats' . rand(1,100000));

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);
        $em->flush();

        return new Response('<html><body>Job created!</body></html>');
    }

    /**
     * @Route("/job")
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $jobs = $em->getRepository('AppBundle:Job')
            ->findAllPublishedOrderedByDatePosted();

        return $this->render('job/list.html.twig', [
            'jobs' => $jobs
        ]);

    }

    /**
     * @Route("/job/{id}", name="job_show")
     */
    public function showAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('AppBundle:Job')
            ->findOneBy(['id' => $id]);

        if(!$job) {
            throw $this->createNotFoundException('No job found');
        }

//        $templating = $this->container->get('templating');
//        $html = $templating->render('job/show.html.twig', [
//            'jobId' => $jobId
//        ]);
//        return new Response($html);

        return $this->render('job/show.html.twig', [
            'job' => $job
        ]);

    }
}