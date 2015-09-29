<?php

abstract class JobPosting
{                 // aka. http://schema.org/JobPosting
    private $baseSalary = null;             // Number  or PriceSpecification 	The base salary of the job or of an employee in an EmployeeRole.
    private $benefits = null;               // Text 	Description of benefits associated with the job.
    private $datePosted = null;             // Date 	Publication date for the job posting.
    private $educationRequirements = null;  // Text 	Educational background needed for the position.
    private $employmentType = null;         // Text 	Type of employment (e.g. full-time, part-time, contract, temporary, seasonal, internship).
    private $experienceRequirements = null; // Text 	Description of skills and experience needed for the position.
    private $hiringOrganization = null;     // Organization 	Organization offering the job position.
    private $incentives = null;             // Text 	Description of bonus and commission compensation aspects of the job.
    private $industry = null;               // Text 	The industry associated with the job position.
    private $jobLocation = null;            // Place 	A (typically single) geographic location associated with the job position.
    private $occupationalCategory = null;   // Text 	Category or categories describing the job. Use BLS O*NET-SOC taxonomy: http://www.onetcenter.org/taxonomy.html. Ideally includes textual label and formal code, with the property repeated for each applicable value.
    private $qualifications = null;         // Text 	Specific qualifications required for this role.
    private $responsibilities = null;       // Text 	Responsibilities associated with this role.
    private $salaryCurrency = null;         // Text 	The currency (coded using ISO 4217, http://en.wikipedia.org/wiki/ISO_4217 ) used for the main salary information in this job posting or for this employee.
    private $skills = null;                 // Text 	Skills required to fulfill this role.
    private $specialCommitments = null;     // Text 	Any special commitments associated with this job posting. Valid entries include VeteranCommit, MilitarySpouseCommit, etc.
    private $title = null;                  // Text 	The title of the job.
    private $workHours = null;              // Text 	The typical working hours for this job (e.g. 1st shift, night shift, 8am-5pm).

    protected $additionalType = null;       // URL 	An additional type for the item, typically used for adding more specific types from external vocabularies in microdata syntax. This is a relationship between something and a class that the thing is in. In RDFa syntax, it is better to use the native RDFa syntax - the 'typeof' attribute - for multiple types. Schema.org tools may have only weaker understanding of extra types, in particular those defined externally.
    protected $alternateName = null;        // Text 	An alias for the item.
    protected $description = null;          // Text 	A short description of the item.
    protected $image = null;                // URL  or ImageObject
    protected $name = null;                 // Text 	The name of the item.
    protected $potentialAction = null;      // Action 	Indicates a potential Action, which describes an idealized action in which this thing would play an 'object' role.
    protected $sameAs = null;               // URL 	URL of a reference Web page that unambiguously indicates the item's identity. E.g. the URL of the item's Wikipedia page, Freebase page, or official website.
    protected $url = null;                  // URL 	URL of the item.
}