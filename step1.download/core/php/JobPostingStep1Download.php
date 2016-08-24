<?php

/**
 * The class of JobPosting with all extra properties added.
 * Some extra properties may be added from time to time, because extractable blocks of information differ from project to project.
 * The required properties are: id (unique id of any kind in the source system) and html (the content of the job ad).
 * However, sometimes we could extract some more interesting information (for example, statistics of an ad)
 *  then we add that extra information to the object as well.
 */
class JobPostingStep1Download extends JobPosting {

    // project name (aka. source system, project's directory name, etc.) For example: "cvbankas.lt"
    public $project = null;

    // unique id of any kind in the source system
    public $id = null;

    // the content of the job ad in html format
    public $html = null;

    // statistics in html format
    public $statistics = null;

    // url
    protected $url = null;

    // What was the job ad downloaded?
    protected $downloaded_time = null;



    public function set($property, $value)
    {
        $this->$property = $value;
    }

    public function get($property)
    {
        if(property_exists($this, $property)) {
            return $this->$property;
        } else {
            return null;
        }
    }

    /**
     * Given property names are treated as NOT normalized (formated in raw html or so).
     * Those property names will be normalized (split into smaller information containers, eg.: baseSalary as number, title as text, etc.)
     */
    public function getPropertyNamesToNormalizeFrom() {
        return (array)get_object_vars($this);
    }

}