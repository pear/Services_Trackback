<?php
// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/Akismet.php';

// Testdata
require_once dirname(__FILE__).'/trackback_data.php';

// Akismet.com API key
require_once 'akismet_key.php';


class Services_Trackback_SpamCheck_Akismet_Test extends PHPUnit_Framework_TestCase
{

    var $trackbacks = array();

    var $spamCheck;

    var $options = array();

    function setUp() {
        global $trackbackData;
        global $akismetApiKey;
        if (!isset($akismetApiKey) || $akismetApiKey === false) {
            $this->markTestSkipped("No Akismet API key defined - see test/akismet_key.php for more");
        }
        foreach ($trackbackData as $id => $set) {
            $this->trackbacks[$id] = Services_Trackback::create($set);
            $this->trackbacks[$id]->set(
                'extra',
                array(
                    'HTTP_REFERER' => 'http://www.example.com',
                    'HTTP_USER_AGENT' => 'Test',
                )
            );
        }
        $this->_options = array(
            'url' => 'http://www.schlitt.info/applications/blog/',
            'key' => $akismetApiKey,
        );
        $this->spamCheck = Services_Trackback_SpamCheck::create('Akismet', $this->_options);
    }

    function test_create() {
        $realCheck = new Services_Trackback_SpamCheck_Akismet($this->_options);
        $this->assertTrue($this->spamCheck == $realCheck);
    }

    function test_check_failure_nospam() {
        $this->assertFalse($this->spamCheck->check($this->trackbacks['nospam']));
    }

    function test_check_failure_undetected() {
        $this->assertFalse($this->spamCheck->check($this->trackbacks['undetected']));
    }

    function test_check_success_all() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['all']));
    }

    function test_getResults() {
        $this->spamCheck->check($this->trackbacks['all']);
        $results = $this->spamCheck->getResults();
        $this->assertTrue($results[0]);
    }

    function test_reset() {
        $this->spamCheck->check($this->trackbacks['all']);
        $this->spamCheck->reset();
        $fakeCheck = Services_Trackback_SpamCheck::create('Akismet', $this->_options);
        $this->assertTrue($this->spamCheck == $fakeCheck);
    }

    function test_verifyKey_success() {
        $this->assertTrue($this->spamCheck->verifyKey());
    }

    function test_verifyKey_failure() {
        $this->spamCheck->_options['key'] = 'foobar';
        $this->assertFalse($this->spamCheck->verifyKey());
    }

    function test_reportSpam_success() {
        $this->assertTrue($this->spamCheck->submitSpam($this->trackbacks['all']));
    }

}
