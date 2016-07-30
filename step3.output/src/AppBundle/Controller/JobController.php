<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-07-29
 * Time: 22:27
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class JobController extends Controller
{
    /**
     * @Route("/job/{jobId}")
     */
    public function showAction($jobId)
    {
        $templating = $this->container->get('templating');
        $html = $templating->render('job/show.html.twig', [
            'name' => $jobId
        ]);
        return new Response($html);
    }
}