<?php
// {{{ require_once

// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/DNSBL.php';

// Testdata
require_once dirname(__FILE__).'/trackback_data.php';

// }}}

class Services_Trackback_SpamCheck_DNSBL_Test extends PHPUnit_Framework_TestCase
{

    public $trackbacks = array();

    public $spamCheck;

    public function setUp() {
        global $trackbackData;
        foreach ($trackbackData as $id => $set) {
            $this->trackbacks[$id] = Services_Trackback::create($set);
        }
        $this->spamCheck = Services_Trackback_SpamCheck::create('DNSBL');
    }

    public function test_create() {
        $realCheck = new Services_Trackback_SpamCheck_DNSBL();
        $this->assertTrue($this->spamCheck == $realCheck);
    }

    public function test_check_failure_nospam() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['nospam']));
    }

    public function test_check_failure_undetected() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['undetected']));
    }

    public function test_check_success_all() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['all']));
    }

    public function test_check_success_host() {
        $this->assertTrue($this->spamCheck->check($this->trackbacks['host']));
    }

    public function test_check_failure_title() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['title']));
    }

    public function test_check_failure_excerpt() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['excerpt']));
    }

    public function test_check_failure_url() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['url']));
    }

    public function test_check_failure_blog_name() {
        $this->assertTrue(!$this->spamCheck->check($this->trackbacks['blog_name']));
    }

    public function test_getResults() {
        $this->spamCheck->check($this->trackbacks['all']);
        $results = $this->spamCheck->getResults();
        $this->assertTrue($results[0]);
    }

    public function test_reset() {
        $this->spamCheck->check($this->trackbacks['all']);
        $this->spamCheck->reset();

        $fakeCheck = Services_Trackback_SpamCheck::create('DNSBL');
        $fakeCheck->getDNSBL()->setBlacklists(array('bl.spamcop.net'));

        $this->assertTrue($this->spamCheck == $fakeCheck);
    }

}
