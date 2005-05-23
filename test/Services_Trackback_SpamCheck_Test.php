<?php

// Includepath for local CVS development
// set_include_path('/cvs/pear/Services_Trackback'.PATH_SEPARATOR.get_include_path());

    // {{{ require_once

// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/Wordlist.php';

// Unittest suite
require_once 'PHPUnit.php';

// Testdata
require_once 'test/trackback_data.php';

    // }}}

class Webservices_Trackback_SpamCheck_TestCase extends PHPUnit_TestCase
{

    var $trackbacks = array();

    var $spamCheck;
    
    // {{{ Webservices_Trackback_SpamCheck_TestCase()
    
    // constructor of the test suite
    function Webservices_Trackback_TestCase($name) {
       $this->PHPUnit_TestCase($name);
    }

    // }}}
    // {{{ setup()
    
    function setUp() {
        global $trackbackData;
        $this->_trackbacks['nospam'] = Services_Trackback::create($trackbackData['nospam']);
        $this->_trackbacks['spam'] =  Services_Trackback::create($trackbackData['all']);
        $this->spamCheck = new Services_Trackback_SpamCheck_Wordlist();
    }

    // }}}
    // {{{ tearDown()
    
    function tearDown() {
    }

    // }}}
    // {{{ Test create()

    function test_create() {
        $realCheck = Services_Trackback_SpamCheck::create('Wordlist');
        $this->assertTrue($this->spamCheck == $realCheck);
    }

    // }}}
    // {{{ Test check()

    function test_check_success() {
        $this->assertTrue($this->spamCheck->check($this->_trackbacks['spam']));
    }
    
    function test_check_failure() {
        $this->assertTrue(!$this->spamCheck->check($this->_trackbacks['nospam']));
    }

    // }}}
    // {{{ Test getResults()

    function test_getResults() {
        $this->spamCheck->check($this->_trackbacks['spam']);
        $results = $this->spamCheck->getResults();
        $this->assertTrue($results[6]);
    }

    // }}}
    // {{{ Test reset()

    function test_reset() {
        $this->spamCheck->check($this->_trackbacks['spam']);
        $this->spamCheck->_results = array();
        $fakeCheck = Services_Trackback_SpamCheck::create('Wordlist');
        $this->assertTrue($this->spamCheck == $fakeCheck);
    }

    // }}}

}
$suite  = new PHPUnit_TestSuite("Webservices_Trackback_SpamCheck_TestCase");
$result = PHPUnit::run($suite);

echo $result->toString();

?>
