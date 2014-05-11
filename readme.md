# MyConcordiaApi

## Description

This [Composer](https://getcomposer.org/) library provides a programmatically convenient interface for the purpose of interfacing with the [MyConcordia](https://www.myconcordia.ca/) student portal.

Currently, there is an implementation for the retrieval of the student record transcript as well as an associated parser for the retrieval of registered courses (and in turn, grades).

## Usage

The following examples assume that the student credentials for the MyConcordia portal are simply `netname` and `password`.

It also assumes that the library has been properly included in the callee script or has been installed via Composer.

### Retrieving all courses.

In order to get a basic listing of all the courses and the associated grades, we can simply perform the following:

    $portal = new MyConcordiaApi\Portal("netname", "password");
    $courses = $portal->getTranscriptCourses();

    foreach ($courses as $c) {
        echo $c->code . "\t" . $c->grade . "\n";
    }

## License

The library is licensed under the [MIT license](http://opensource.org/licenses/MIT).