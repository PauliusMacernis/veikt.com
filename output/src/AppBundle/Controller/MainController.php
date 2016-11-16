<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * Class MainController
 * @package AppBundle\Controller
 */
class MainController extends Controller
{
    public function homepageAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('base.under_construction.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);


    }
}
