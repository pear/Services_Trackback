<?php

// Includepath for local CVS development
set_include_path('/cvs/pear/Services_Trackback'.PATH_SEPARATOR.get_include_path());

    // {{{ require_once

require_once 'Services/Trackback.php';
require_once 'PHPUnit.php';

    // }}}

class Webservices_Trackback_SpamCheck_TestCase extends PHPUnit_TestCase
{
    
    // {{{ Webservices_Trackback_SpamCheck_TestCase()
    
    // constructor of the test suite
    function Webservices_Trackback_TestCase($name) {
       $this->PHPUnit_TestCase($name);
    }

    // }}}
    // {{{ setup()
    
    function setUp() {
        $this->_trackback = new Services_Trackback();
    }

    // }}}
    // {{{ tearDown()
    
    function tearDown() {
    }

    // }}}
    // {{{ Test create()

    function test_create() {
        $data = array(
            'title' => 'Test title.',
            'id'    => 'Test'
        );
        $options = array(
            'useragent'         => 'Mozilla 10.0',
            'strictness'        => SERVICES_TRACKBACK_STRICTNESS_HIGH,
            'timeout'           => 10,
            'allowRedirects'    => false,
            'maxRedirects'      => 0,
            'fetchlines'        => 100,
        );
        $fakeTrack = new Services_Trackback;
        $fakeTrack->_options = $options;
        $fakeTrack->_data = $data;
        $this->assertTrue(Services_Trackback::create($data, $options) == $fakeTrack);
    }

    // }}}

}

$suite  = new PHPUnit_TestSuite("Webservices_Trackback_TestCase");
$result = PHPUnit::run($suite);

echo $result->toString();

?>
