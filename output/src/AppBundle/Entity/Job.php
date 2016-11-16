<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-08-13
 * Time: 18:37
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\JobNote;
use Symfony\Component\Validator\Constraints as Assert;

// aka. http://schema.org/JobPosting

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JobRepository")
 * @ORM\Table(name="job")
 */
class Job
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SubFamily")
     * @ORM\JoinColumn(nullable=true)
     */
    private $subFamily;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=4, nullable=true)
     */
    private $baseSalary = null;             // Number  or PriceSpecification 	The base salary of the job or of an employee in an EmployeeRole.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $benefits = null;               // Text 	Description of benefits associated with the job.
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datePosted = null;             // Date 	Publication date for the job posting.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $educationRequirements = null;  // Text 	Educational background needed for the position.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $employmentType = null;         // Text 	Type of employment (e.g. full-time, part-time, contract, temporary, seasonal, internship).
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $experienceRequirements = null; // Text 	Description of skills and experience needed for the position.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hiringOrganization = null;     // Organization 	Organization offering the job position.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $incentives = null;             // Text 	Description of bonus and commission compensation aspects of the job.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $industry = null;               // Text 	The industry associated with the job position.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $jobLocation = null;            // Place 	A (typically single) geographic location associated with the job position.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $occupationalCategory = null;   // Text 	Category or categories describing the job. Use BLS O*NET-SOC taxonomy: http://www.onetcenter.org/taxonomy.html. Ideally includes textual label and formal code, with the property repeated for each applicable value.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $qualifications = null;         // Text 	Specific qualifications required for this role.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $responsibilities = null;       // Text 	Responsibilities associated with this role.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $salaryCurrency = null;         // Text 	The currency (coded using ISO 4217, http://en.wikipedia.org/wiki/ISO_4217 ) used for the main salary information in this job posting or for this employee.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $skills = null;                 // Text 	Skills required to fulfill this role.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $specialCommitments = null;     // Text 	Any special commitments associated with this job posting. Valid entries include VeteranCommit, MilitarySpouseCommit, etc.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title = null;                  // Text 	The title of the job.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $workHours = null;              // Text 	The typical working hours for this job (e.g. 1st shift, night shift, 8am-5pm).
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $additionalType = null;       // URL 	An additional type for the item, typically used for adding more specific types from external vocabularies in microdata syntax. This is a relationship between something and a class that the thing is in. In RDFa syntax, it is better to use the native RDFa syntax - the 'typeof' attribute - for multiple types. Schema.org tools may have only weaker understanding of extra types, in particular those defined externally.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $alternateName = null;        // Text 	An alias for the item.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description = null;          // Text 	A short description of the item.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image = null;                        // URL  or ImageObject
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name = null;                         // Text 	The name of the item.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $potentialAction = null;              // Action 	Indicates a potential Action, which describes an idealized action in which this thing would play an 'object' role.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sameAs = null;                       // URL 	URL of a reference Web page that unambiguously indicates the item's identity. E.g. the URL of the item's Wikipedia page, Freebase page, or official website.
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url = null;                          // URL 	URL of the item.
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=false)
     */
    public $step1_id = null;                        // Unique id of any kind in the source system
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=false)
     */
    public $step1_html = null;                      // The content of the job ad in html format
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text", nullable=false)
     */
    public $step1_statistics = null;                // Statistics in html format
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=false)
     */
    public $step1_project = null;
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", nullable=false)
     */
    public $step1_url = null;
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $step1_downloadedTime = null;
    /**
     * @ORM\Column(type="boolean", options={"default" : true, "unsigned"=true})
     */
    private $isPublished = true;                    //@todo: Is it default for insert query only? Not for column itself?
    /**
     * @ORM\OneToMany(targetEntity="JobNote", mappedBy="job")
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    private $notes;


    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBaseSalary()
    {
        return $this->baseSalary;
    }

    /**
     * @param mixed $baseSalary
     */
    public function setBaseSalary($baseSalary)
    {
        $this->baseSalary = $baseSalary;
    }

    /**
     * @return mixed
     */
    public function getBenefits()
    {
        return $this->benefits;
    }

    /**
     * @param mixed $benefits
     */
    public function setBenefits($benefits)
    {
        $this->benefits = $benefits;
    }

    /**
     * @return mixed
     */
    public function getDatePosted()
    {
        return $this->datePosted;
    }

    /**
     * @param mixed $datePosted
     */
    public function setDatePosted($datePosted)
    {
        $this->datePosted = $datePosted;
    }

    /**
     * @return mixed
     */
    public function getEducationRequirements()
    {
        return $this->educationRequirements;
    }

    /**
     * @param mixed $educationRequirements
     */
    public function setEducationRequirements($educationRequirements)
    {
        $this->educationRequirements = $educationRequirements;
    }

    /**
     * @return mixed
     */
    public function getEmploymentType()
    {
        return $this->employmentType;
    }

    /**
     * @param mixed $employmentType
     */
    public function setEmploymentType($employmentType)
    {
        $this->employmentType = $employmentType;
    }

    /**
     * @return mixed
     */
    public function getExperienceRequirements()
    {
        return $this->experienceRequirements;
    }

    /**
     * @param mixed $experienceRequirements
     */
    public function setExperienceRequirements($experienceRequirements)
    {
        $this->experienceRequirements = $experienceRequirements;
    }

    /**
     * @return mixed
     */
    public function getHiringOrganization()
    {
        return $this->hiringOrganization;
    }

    /**
     * @param mixed $hiringOrganization
     */
    public function setHiringOrganization($hiringOrganization)
    {
        $this->hiringOrganization = $hiringOrganization;
    }

    /**
     * @return mixed
     */
    public function getIncentives()
    {
        return $this->incentives;
    }

    /**
     * @param mixed $incentives
     */
    public function setIncentives($incentives)
    {
        $this->incentives = $incentives;
    }

    /**
     * @return mixed
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * @param mixed $industry
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;
    }

    /**
     * @return mixed
     */
    public function getJobLocation()
    {
        return $this->jobLocation;
    }

    /**
     * @param mixed $jobLocation
     */
    public function setJobLocation($jobLocation)
    {
        $this->jobLocation = $jobLocation;
    }

    /**
     * @return mixed
     */
    public function getOccupationalCategory()
    {
        return $this->occupationalCategory;
    }

    /**
     * @param mixed $occupationalCategory
     */
    public function setOccupationalCategory($occupationalCategory)
    {
        $this->occupationalCategory = $occupationalCategory;
    }

    /**
     * @return mixed
     */
    public function getQualifications()
    {
        return $this->qualifications;
    }

    /**
     * @param mixed $qualifications
     */
    public function setQualifications($qualifications)
    {
        $this->qualifications = $qualifications;
    }

    /**
     * @return mixed
     */
    public function getResponsibilities()
    {
        return $this->responsibilities;
    }

    /**
     * @param mixed $responsibilities
     */
    public function setResponsibilities($responsibilities)
    {
        $this->responsibilities = $responsibilities;
    }

    /**
     * @return mixed
     */
    public function getSalaryCurrency()
    {
        return $this->salaryCurrency;
    }

    /**
     * @param mixed $salaryCurrency
     */
    public function setSalaryCurrency($salaryCurrency)
    {
        $this->salaryCurrency = $salaryCurrency;
    }

    /**
     * @return mixed
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * @param mixed $skills
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
    }

    /**
     * @return mixed
     */
    public function getSpecialCommitments()
    {
        return $this->specialCommitments;
    }

    /**
     * @param mixed $specialCommitments
     */
    public function setSpecialCommitments($specialCommitments)
    {
        $this->specialCommitments = $specialCommitments;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getWorkHours()
    {
        return $this->workHours;
    }

    /**
     * @param mixed $workHours
     */
    public function setWorkHours($workHours)
    {
        $this->workHours = $workHours;
    }

    /**
     * @return mixed
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }



    /**
     * @param mixed $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }



    /**
     * @return mixed
     */
    public function getAdditionalType()
    {
        return $this->additionalType;
    }

    /**
     * @param mixed $additionalType
     */
    public function setAdditionalType($additionalType)
    {
        $this->additionalType = $additionalType;
    }

    /**
     * @return mixed
     */
    public function getAlternateName()
    {
        return $this->alternateName;
    }

    /**
     * @param mixed $alternateName
     */
    public function setAlternateName($alternateName)
    {
        $this->alternateName = $alternateName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPotentialAction()
    {
        return $this->potentialAction;
    }

    /**
     * @param mixed $potentialAction
     */
    public function setPotentialAction($potentialAction)
    {
        $this->potentialAction = $potentialAction;
    }

    /**
     * @return mixed
     */
    public function getSameAs()
    {
        return $this->sameAs;
    }

    /**
     * @param mixed $sameAs
     */
    public function setSameAs($sameAs)
    {
        $this->sameAs = $sameAs;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getStep1Id()
    {
        return $this->step1_id;
    }

    /**
     * @param mixed $step1_id
     */
    public function setStep1Id($step1_id)
    {
        $this->step1_id = $step1_id;
    }

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

    /**
     * @return mixed
     */
    public function getStep1Statistics()
    {
        return $this->step1_statistics;
    }

    /**
     * @param mixed $step1_statistics
     */
    public function setStep1Statistics($step1_statistics)
    {
        $this->step1_statistics = $step1_statistics;
    }

    /**
     * @return mixed
     */
    public function getStep1Project()
    {
        return $this->step1_project;
    }

    /**
     * @param mixed $step1_project
     */
    public function setStep1Project($step1_project)
    {
        $this->step1_project = $step1_project;
    }

    /**
     * @return mixed
     */
    public function getStep1Url()
    {
        return $this->step1_url;
    }

    /**
     * @param mixed $step1_url
     */
    public function setStep1Url($step1_url)
    {
        $this->step1_url = $step1_url;
    }

    /**
     * @return mixed
     */
    public function getStep1DownloadedTime()
    {
        return $this->step1_downloadedTime;
    }

    /**
     * @param mixed $step1_downloadedTime
     */
    public function setStep1DownloadedTime($step1_downloadedTime)
    {
        $this->step1_downloadedTime = $step1_downloadedTime;
    }



    public function getStep1UpdatedAt() {
        return new \DateTime('-' . rand(0, 24) . ' hours');
    }

    /**
     * @return ArrayCollection|JobNote[]
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return ArrayCollection|SubFamily[]
     */
    public function getSubFamily()
    {
        return $this->subFamily;
    }

    /**
     * @param mixed $subFamily
     */
    public function setSubFamily($subFamily)
    {
        $this->subFamily = $subFamily;
    }



}