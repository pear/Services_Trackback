<?php
if (!defined('PHPUNIT_MAIN_METHOD')) {
    define('PHPUNIT_MAIN_METHOD', 'Services_Trackback_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Services_Trackback_SpamCheck_Akismet_Test.php';
require_once 'Services_Trackback_SpamCheck_DNSBL_Test.php';
require_once 'Services_Trackback_SpamCheck_Regex_Test.php';
require_once 'Services_Trackback_SpamCheck_SURBL_Test.php';
require_once 'Services_Trackback_SpamCheck_Wordlist_Test.php';
require_once 'Services_Trackback_Test.php';

class Services_Trackback_AllTests {

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('Services_Trackback_Test');
        $suite->addTestSuite('Services_Trackback_SpamCheck_Wordlist_Test');
        $suite->addTestSuite('Services_Trackback_SpamCheck_SURBL_Test');
        $suite->addTestSuite('Services_Trackback_SpamCheck_Regex_Test');
        $suite->addTestSuite('Services_Trackback_SpamCheck_DNSBL_Test');
        $suite->addTestSuite('Services_Trackback_SpamCheck_Akismet_Test');

        return $suite;
    }
}

if (PHPUNIT_MAIN_METHOD == 'Services_Trackback_AllTests::main') {
    Services_Trackback_AllTests::main();
}