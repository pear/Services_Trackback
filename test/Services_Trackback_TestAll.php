<?php

echo "\n\nRunning test suite for Services_Trackback\n\n";
require_once 'Services_Trackback_Test.php';

echo "\n\nRunning test suite for Services_Trackback_SpamCheck\n\n";
require_once 'Services_Trackback_SpamCheck_Test.php';

echo "\n\nRunning test suite for Services_Trackback_SpamCheck_DNSBL\n\n";
require_once 'Services_Trackback_SpamCheck_DNSBL_Test.php';
echo "\n\nRunning test suite for Services_Trackback_SpamCheck_Regex\n\n";
require_once 'Services_Trackback_SpamCheck_Regex_Test.php';
echo "\n\nRunning test suite for Services_Trackback_SpamCheck_SURBL\n\n";
require_once 'Services_Trackback_SpamCheck_SURBL_Test.php';
echo "\n\nRunning test suite for Services_Trackback_SpamCheck_Wordlist\n\n";
require_once 'Services_Trackback_SpamCheck_Wordlist_Test.php';

?>
