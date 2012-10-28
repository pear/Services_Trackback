<?php
// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/SURBL.php';

// Unittest suite
require_once 'PHPUnit/Framework/TestCase.php';

// Testdata
require_once dirname(__FILE__).'/trackback_data.php';

class Services_Trackback_SpamCheck_SURBL_Test extends PHPUnit_Framework_TestCase
{

    protected $trackbacks = array();

    protected $spamCheck;

    function setUp() {
        global $trackbackData;
        foreach ($trackbackData as $id => $set) {
            $this->trackbacks[$id] = Services_Trackback::create($set);
        }
        $this->spamCheck = Services_Trackback_SpamCheck::create('SURBL');
    }

    function test_create() {
        $realCheck = new Services_Trackback_SpamCheck_SURBL();
        $this->assertEquals($this->spamCheck, $realCheck);
    }

    function test_check_failure_nospam() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['nospam']));
    }

    function test_check_failure_undetected() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['undetected']));
    }

    function test_check_success_all() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['all']));
    }

    function test_check_success_host() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['host']));
    }

    function test_check_failure_title() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['title']));
    }

    function test_check_success_excerpt() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['excerpt']));
    }

    function test_check_success_url() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['url']));
    }

    function test_check_success() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['blog_name']));
    }

    function test_getResults() {
        $this->spamCheck->check($this->trackbacks['all']);
        $results = $this->spamCheck->getResults();
        $this->assertTrue($results[0]);
    }

    function test_reset() {
        $this->spamCheck->check($this->trackbacks['all']);
        $this->spamCheck->reset();

        $fakeCheck = Services_Trackback_SpamCheck::create('SURBL');
        $this->assertEquals($this->spamCheck, $fakeCheck);
    }

}
