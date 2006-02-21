<?php

    // {{{ require_once

// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/Akismet.php';

// Unittest suite
require_once 'PHPUnit.php';

// Testdata
require_once dirname(__FILE__).'/trackback_data.php';

// Akismet.com API key
require_once 'test/akismet_key.php';

    // }}}

class Webservices_Trackback_SpamCheck_Akismet_TestCase extends PHPUnit_TestCase
{

    var $trackbacks = array();

    var $spamCheck;

    var $options = array();
    
    // {{{ Webservices_Trackback_SpamCheck_Akismet_TestCase()
    
    // constructor of the test suite
    function Webservices_Trackback_SpamCheck_Akismet_TestCase($name) {
       $this->PHPUnit_TestCase($name);
    }

    // }}}
    // {{{ setup()
    
    function setUp() {
        global $trackbackData;
        global $akismetApiKey;
        if (!isset($akismetApiKey) || $akismetApiKey === false) {
            return false;
        }
        foreach ($trackbackData as $id => $set) {
            $this->trackbacks[$id] = Services_Trackback::create($set);
            $this->trackbacks[$id]->set(
                'extra', 
                array(
                    'REFERER' => 'http://www.example.com',
                    'USER_AGENT' => 'Test',
                )
            );
        }
        $this->_options = array(
            'url' => 'http://www.schlitt.info/applications/blog/',
            'key' => $akismetApiKey,
        );
        $this->spamCheck = Services_Trackback_SpamCheck::create('Akismet', $this->_options);
    }

    // }}}
    // {{{ tearDown()
    
    function tearDown() {
    }

    // }}}
    // {{{ Test create()

    function test_create() {
        $realCheck = new Services_Trackback_SpamCheck_Akismet($this->_options);
        $this->assertTrue($this->spamCheck == $realCheck);
    }

    // }}}
    // {{{ Test check()
    function test_check_failure_nospam() {
        $this->assertFalse($this->spamCheck->check($this->trackbacks['nospam']));
    }
    function test_check_failure_undetected() {
        $this->assertFalse($this->spamCheck->check($this->trackbacks['undetected']));
    }
    function test_check_success_all() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['all']));
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
        $this->spamCheck->reset();
        $fakeCheck = Services_Trackback_SpamCheck::create('Akismet', $this->_options);
        $this->assertTrue($this->spamCheck == $fakeCheck);
    }

    // }}}
    // {{{ Test verifyKey()

    function test_verifyKey_success() {
        $this->assertTrue($this->spamCheck->verifyKey());
    }
    
    function test_verifyKey_failure() {
        $this->spamCheck->_options['key'] = 'foobar';
        $this->assertFalse($this->spamCheck->verifyKey());
    }

    // }}}
    // {{{ Test reportSpam()

    function test_reportSpam_success() {
        $this->assertTrue($this->spamCheck->submitSpam($this->trackbacks['all']));
    }

    // }}}

}

if (isset($akismetApiKey) && $akismetApiKey !== false) {
    $suite  = new PHPUnit_TestSuite("Webservices_Trackback_SpamCheck_Akismet_TestCase");
    $result = PHPUnit::run($suite);
    echo $result->toString();
} else {
    echo 'Skipped test case "Webservices_Trackback_SpamCheck_Akismet_TestCase" because Akismet API key is missing. Please configure it in test/akismet_key.php!';
}

?>
