<?php

namespace MyConcordiaApi\Parser;

use MyConcordiaApi\Model\Course;

class CourseParser
{
    /**
     * Contains the DOMDocument object for the transcript page.
     *
     * @var DOMDocument
     */
    protected $__transcriptDOM = null;

    /**
     * Contains all the courses.
     *
     * @var array
     */
    protected $courses = null;

    /**
     * @param DOMDocument $transcriptDOM
     */
    public function __construct(DOMDocument $transcriptDOM)
    {
        parent::__construct();

        $this->__transcriptDOM = $transcriptDOM;

        $this->generateCourses();
    }

    /**
     * Retrieves a collection of all courses as an array.
     *
     * @return array
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * Retrieves a specific Course based on it's code.
     *
     * @param  string  $courseCode
     * @return Course
     */
    public function getCourseByCode($courseCode)
    {
        $courseCode = strtoupper($courseCode);
        $courseCode = preg_replace('/\s+/', '', $courseCode);

        return $this->courses[$courseCode];
    }

    /**
     * Populates the `$courses` instance variable.
     */
    protected function generateCourses()
    {
        /**
         * Let's go through the archane paths that lead us to the actual
         * table of grades.
         */
        $mainTable = $this->__transcriptDOM
            ->getElementsByTagName('table')->item(0);

        $rowOfGrades = $maintable->childNodes->item(4);
        $gradesTable = $rowOfGrades->firstChild->firstChild;

        $this->courses = [];

        foreach ($gradesTable->childNodes as $row) {
            // The rows containing the grades have 13 columns.
            if ($row->childNodes->length == 13) {
                $data = $row->childNodes;
                $course = new Course;

                $course->code          = strtoupper($data->item(0)->nodeValue.$data->item(1)->nodeValue);
                $course->semester      = $data->item(2)->nodeValue;
                $course->section       = $data->item(3)->nodeValue;
                $course->description   = $data->item(4)->nodeValue;
                $course->creditsWorth  = $data->item(5)->nodeValue;
                $course->grade         = $data->item(6)->nodeValue;
                $course->gpaEarned     = $data->item(7)->nodeValue;
                $course->classAvg      = $data->item(8)->nodeValue;
                $course->classSize     = $data->item(9)->nodeValue;
                $course->creditsEarned = $data->item(10)->nodeValue;

                $this->courses[$course->code] = $course;
            }
        }
    }
}