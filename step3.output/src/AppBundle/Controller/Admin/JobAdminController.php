<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-09-24
 * Time: 17:18
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
    public function newAction()
    {
        // let's go to work!
    }
}
