<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-09-25
 * Time: 14:42
 */

namespace AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

class JobSearchFormTypeData
{

    public $step1_html = null;                      // The content of the job ad in html format

    /**
     * @return mixed
     */
    public function getStep1Html()
    {
        return $this->step1_html;
    }

    /**
     * @param mixed $step1_html
     */
    public function setStep1Html($step1_html)
    {
        $this->step1_html = $step1_html;
    }


}