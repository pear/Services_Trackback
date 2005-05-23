<?php

// Includepath for local CVS development
// set_include_path('/cvs/pear/Services_Trackback'.PATH_SEPARATOR.get_include_path());

    // {{{ require_once

// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/Regex.php';

// Unittest suite
require_once 'PHPUnit.php';

// Testdata
require_once 'test/trackback_data.php';

    // }}}

class Webservices_Trackback_SpamCheck_Regex_TestCase extends PHPUnit_TestCase
{

    var $trackbacks = array();

    var $spamCheck;
    
    // {{{ Webservices_Trackback_SpamCheck_TestCase()
    
    // constructor of the test suite
    function Webservices_Trackback_SpamCheck_Regex_TestCase($name) {
       $this->PHPUnit_TestCase($name);
    }

    // }}}
    // {{{ setup()
    
    function setUp() {
        global $trackbackData;
        foreach ($trackbackData as $id => $set) {
            $this->trackbacks[$id] = Services_Trackback::create($set);
        }
        $this->spamCheck = Services_Trackback_SpamCheck::create('Regex');
    }

    // }}}
    // {{{ tearDown()
    
    function tearDown() {
    }

    // }}}
    // {{{ Test create()

    function test_create() {
        $realCheck = new Services_Trackback_SpamCheck_Regex();
        $this->assertTrue($this->spamCheck == $realCheck);
    }

    // }}}
    // {{{ Test check()
    function test_check_failure_nospam() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['nospam']));
    }
    function test_check_failure_undetected() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['undetected']));
    }
    function test_check_success_all() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['all']));
    }
    function test_check_failure_host() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['host']));
    }
    function test_check_success_title() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['title']));
    }
    function test_check_success_excerpt() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['excerpt']));
    }
    function test_check_success_url() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['url']));
    }
    function test_check_success_blog_name() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['blog_name']));
    }
    // }}}
    // {{{ Test getResults()

    function test_getResults() {
        $this->spamCheck->check($this->trackbacks['all']);
        $results = $this->spamCheck->getResults();
        $this->assertTrue($results[0]);
    }

    // }}}
    // {{{ Test reset()

    function test_reset() {
        $this->spamCheck->check($this->trackbacks['all']);
        $this->spamCheck->_results = array();
        $fakeCheck = Services_Trackback_SpamCheck::create('Regex');
        $this->assertTrue($this->spamCheck == $fakeCheck);
    }

    // }}}

}
$suite  = new PHPUnit_TestSuite("Webservices_Trackback_SpamCheck_Regex_TestCase");
$result = PHPUnit::run($suite);

echo $result->toString();

?>
