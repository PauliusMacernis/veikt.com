<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-09-24
 * Time: 17:18
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Form\JobFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class JobAdminController extends Controller
{
    /**
     * @Route("/job", name="admin_job_list")
     */
    public function indexAction()
    {
        $jobs = $this->getDoctrine()
            ->getRepository('AppBundle:Job')
            ->findAll();

        return $this->render('admin/job/list.html.twig', array(
            'jobs' => $jobs
        ));
    }

    /**
     * @Route("/job/new", name="admin_job_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(JobFormType::class);

        // only handles data on POST
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $job = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            $this->addFlash('success', 'Job created - you are amazing!');

            return $this->redirectToRoute('admin_job_list');
        }

        return $this->render('admin/job/new.html.twig', [
            'jobForm' => $form->createView(),
        ]);
    }
}
