<?php

// Includepath for local CVS development
// set_include_path('/cvs/pear/Services_Trackback'.PATH_SEPARATOR.get_include_path());

    // {{{ require_once

// Services_Trackback classes
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';

// Unittest suite
require_once 'PHPUnit.php';

// Testdata
require_once dirname(__FILE__).'/trackback_data.php';

    // }}}

class Webservices_Trackback_TestCase extends PHPUnit_TestCase
{
    
    // {{{ Webservices_Trackback_TestCase()
    
    // constructor of the test suite
    function Webservices_Trackback_TestCase($name) {
       $this->PHPUnit_TestCase($name);
    }

    // }}}
    // {{{ setup()
    
    function setUp() {
    }

    // }}}
    // {{{ tearDown()
    
    function tearDown() {
    }

    // }}}
    // {{{ Test create()

    function test_create() {
        global $trackbackData;
        $options = array(
            'strictness'        => SERVICES_TRACKBACK_STRICTNESS_HIGH,
            'timeout'           => 10,
            'fetchlines'        => 100,
            'httpRequest'       => array(
                'allowRedirects'    => false,
                'maxRedirects'      => 0,
                'useragent'         => 'Mozilla 10.0',
            ),
        );
        $fakeTrack = new Services_Trackback;
        $fakeTrack->_options = $options;
        $fakeTrack->_data = $trackbackData['nospam'];
        $this->assertTrue(Services_Trackback::create($trackbackData['nospam'], $options) == $fakeTrack);
    }

    // }}}
    // {{{ Test setOptions()

    function test_setOptions_success() {
        $options = array(
            'strictness'        => SERVICES_TRACKBACK_STRICTNESS_HIGH,
            'timeout'           => 10,
            'fetchlines'        => 100,
            'httpRequest'       => array(
                'allowRedirects'    => false,
                'maxRedirects'      => 0,
                'useragent'         => 'Mozilla 10.0',
            ),
        );
        $fakeTrack = new Services_Trackback;
        $fakeTrack->_options = $options;
        $realTrack = new Services_Trackback;
        $realTrack->setOptions($options);
        $this->assertTrue($realTrack == $fakeTrack);
    }

    // }}}
    // {{{ Test getOptions()

    function test_getOptions_success() {
        $options = array(
            'strictness'        => SERVICES_TRACKBACK_STRICTNESS_HIGH,
            'timeout'           => 10,
            'fetchlines'        => 100,
            'httpRequest'       => array(
                'allowRedirects'    => false,
                'maxRedirects'      => 0,
                'useragent'         => 'Mozilla 10.0',
            ),
        );
        $track = new Services_Trackback;
        $track->_options = $options;
        $this->assertTrue($track->getOptions() == $options);
    }

    // }}}
    // {{{ Test autodiscover()
   
   function test_autodiscover_success()
    {
        $data = array(
            'id' => 'Test',
            'url' => 'http://pear.php.net/package/net_ftp'
        );
        $track1 = Services_Trackback::create($data);
        $track1->autodiscover();

        $data['trackback_url'] = 'http://pear.php.net/trackback/trackback.php?id=Net_FTP';
        $track2 = Services_Trackback::create($data);
        $this->assertTrue($track1 == $track2);
    }
    function test_autodiscover_failure()
    {
        $data = array(
            'id' => 'Test',
            'url' => 'http://pear.php.net/'
        );
        $track1 = Services_Trackback::create($data);
        $res = $track1->autodiscover();
        $this->assertTrue(PEAR::isError($res));
    }
    
    // }}}
    // {{{Test send()

    function test_send()
    {
        global $trackbackData;
        $track = Services_Trackback::create($trackbackData['nospam']);
    }

    // }}}
    // {{{Test getAutodiscoveryCode()

    function test_getAutodiscoveryCode_nocomments()
    {
        global $trackbackData;
        $data = $trackbackData['nospam'];
        
        $xml = <<<EOD
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
    <rdf:Description
        rdf:about="%s"
        dc:identifier="%s"
        dc:title="%s"
        trackback:ping="%s" />
</rdf:RDF>

EOD;
        $xml = sprintf($xml, $data['url'], $data['url'], $data['title'], $data['trackback_url']);
        $track = Services_Trackback::create($data);
        $this->assertTrue($track->getAutodiscoveryCode(false) == $xml);
    }
    function test_getAutodiscoveryCode_comments()
    {
        global $trackbackData;
        $data = $trackbackData['nospam'];
        
        $xml = <<<EOD
<!--
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
    <rdf:Description
        rdf:about="%s"
        dc:identifier="%s"
        dc:title="%s"
        trackback:ping="%s" />
</rdf:RDF>
-->

EOD;
        $xml = sprintf($xml, $data['url'], $data['url'], $data['title'], $data['trackback_url']);
        $track = Services_Trackback::create($data);
        $this->assertTrue($track->getAutodiscoveryCode() == $xml);
    }

    // }}}
    // {{{ Test receive()
    
    function test_receive()
    {
        global $trackbackData;
        $postData = $trackbackData['nospam'];
        $data = $postData;
        
        $data['id'] = 1;
        // Not set during receive()
        // unset($data['host']);
        unset($data['trackback_url']);
        
        $recTrack = Services_Trackback::create(array('id' => 1));
        $recTrack->receive($postData);
        $this->assertTrue($recTrack == Services_Trackback::create($data));
    }

    // }}}
    // {{{ Test getResponseSuccess()
    
    function test_getResponseSuccess()
    {
        $xml = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<response>
<error>0</error>
</response>
EOD;
        $this->assertTrue(Services_Trackback::getResponseSuccess() == $xml);
    }

    // }}}
    // {{{ Test getResponseError()
    
    function test_getResponseError()
    {
        $xml = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<response>
<error>-2</error>
<message>Me &amp; you</message>
</response>
EOD;
        $this->assertTrue(Services_Trackback::getResponseError('Me & you', -2) == $xml);
    }
    
    // }}}
    // {{{ Test addSpamCheck
    
    function test_addSpamCheck_success()
    {
        $trackback = new Services_Trackback();
        $spamCheck = new Services_Trackback_SpamCheck();
        $this->assertTrue($trackback->addSpamCheck($spamCheck));
    }
    
    function test_addSpamCheck_failure()
    {
        $trackback = new Services_Trackback();
        $spamCheck = new Services_Trackback();
        $this->assertTrue(PEAR::isError($trackback->addSpamCheck($spamCheck)));
    }
    
    // }}}
    // {{{ Test createSpamCheck
    
    function test_createSpamCheck_success()
    {
        global $trackbackData;
        $trackback = new Services_Trackback($trackbackData['nospam']);
        $spamCheck = Services_Trackback_SpamCheck::create('DNSBL');
        $this->assertTrue($trackback->createSpamCheck('DNSBL') == $spamCheck);
    }
    
    function test_createSpamCheck_failure()
    {
        global $trackbackData;
        $trackback = new Services_Trackback($trackbackData['nospam']);
        $spamCheck = Services_Trackback_SpamCheck::create('DNS');
        $this->assertTrue(PEAR::isError($spamCheck));
    }
    
    // }}}
    // {{{ Test removeSpamCheck
    
    function test_removeSpamCheck_success()
    {
        $trackback = new Services_Trackback();
        $spamCheck = new Services_Trackback_SpamCheck();
        $trackback->addSpamCheck($spamCheck);
        $this->assertTrue($trackback->removeSpamCheck($spamCheck));
    }
    
    function test_removeSpamCheck_failure()
    {
        $trackback = new Services_Trackback();
        $spamCheck = new Services_Trackback_SpamCheck();
        $trackback->addSpamCheck($spamCheck);
        $spamCheck2 = new Services_Trackback_SpamCheck();
        $this->assertTrue(PEAR::isError($trackback->removeSpamCheck($spamCheck2)));
    }
    
    // }}}
    // {{{ Test _fromArray()

    function test_fromArray() {
        global $trackbackData;
        $fakeTrack = new Services_Trackback;
        $fakeTrack->_data = $trackbackData['nospam'];
        $realTrack = new Services_Trackback;
        $realTrack->_fromArray($trackbackData['nospam']);
        $this->assertTrue($realTrack == $fakeTrack);
    }

    // }}}
    // {{{ Test _getContent()

    function test_getContent() {
        global $trackbackData;
        $trackback = Services_Trackback::create($trackbackData['nospam']);
        $url = 'http://www.example.com';
        $res = <<<EOD
<HTML>
<HEAD>
  <TITLE>Example Web Page</TITLE>
</HEAD> 
<body>  
<p>You have reached this web page by typing &quot;example.com&quot;,
&quot;example.net&quot;,
  or &quot;example.org&quot; into your web browser.</p>
<p>These domain names are reserved for use in documentation and are not available 
  for registration. See <a href="http://www.rfc-editor.org/rfc/rfc2606.txt">RFC 
  2606</a>, Section 3.</p>
</BODY>
</HTML>
EOD;

        $this->assertTrue(trim($trackback->_getContent($url)) == trim($res));
    }

    // }}}
    // {{{ Test _getEncodedData()

    function test_getEncodedData() {
        $in = array(
            'foo' => 'bar & baz',
            'bar' => 'foo << baz',
            'baz' => 'foo && bar'
        );

        $out = array(
            'foo' => 'bar &amp; baz',
            'bar' => 'foo &lt;&lt; baz',
            'baz' => 'foo &amp;&amp; bar'
        );
        
        $this->assertTrue(Services_Trackback::_getEncodedData(array('foo', 'bar', 'baz'), $in) == $out);
    }
    // }}}
    // {{{ Test _getDecodedData()

    function test_getDecodedData() {
        $in = array(
            'foo' => 'bar & baz',
            'bar' => 'foo << baz',
            'baz' => 'foo && bar'
        );

        $out = array(
            'foo' => 'bar & baz',
            'baz' => 'foo && bar'
        );
        
        $this->assertTrue(Services_Trackback::_getDecodedData(array('foo', 'baz'), $in) == $out);
    }

    // }}}
    // {{{ Test _checkData
    
    function test_checkData_true()
    {
        $keys = array('id', 'test');
        $data = array('id' => 1, 'test' => 'x', 'test2' => 0);
        $this->assertTrue(Services_Trackback::_checkData($keys, $data));
    }
    function test_checkData_false()
    {
        $keys = array('id', 'test');
        $data = array('id' => 1, 'test2' => 0);
        $this->assertTrue(PEAR::isError(Services_Trackback::_checkData($keys, $data)));
    }

    // }}}
    // {{{ Test _checkURLs()
    
    function test_checkURLs_true_1()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_LOW;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.net/trackbike/index.htm";
        $this->assertTrue(Services_Trackback::_checkURLs($url1, $url2, $strictness));
    }
    function test_checkURLs_true_2()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_MIDDLE;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackbike/index.htm";
        $this->assertTrue(Services_Trackback::_checkURLs($url1, $url2, $strictness));
    }
    function test_checkURLs_true_3()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_HIGH;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.php";
        $this->assertTrue(Services_Trackback::_checkURLs($url1, $url2, $strictness));
    }


    function test_checkURLs_false_1()
    {
        // No real test, should always return true
        $strictness = SERVICES_TRACKBACK_STRICTNESS_LOW;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "https://test.net/trackbike/index.htm";
        $this->assertTrue(Services_Trackback::_checkURLs($url1, $url2, $strictness));
    }
    function test_checkURLs_false_2()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_MIDDLE;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.net/trackback/index.php";
        $this->assertTrue(PEAR::isError(Services_Trackback::_checkURLs($url1, $url2, $strictness)));
    }
    function test_checkURLs_false_3()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_HIGH;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.htm";
        $this->assertTrue(PEAR::isError(Services_Trackback::_checkURLs($url1, $url2, $strictness)));
    }

    function test_checkURLs_invalid_1()
    {
        // No real test, should always return true
        $strictness = SERVICES_TRACKBACK_STRICTNESS_LOW;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "https://test.net/trackbike/index.htm";
        $this->assertTrue(Services_Trackback::_checkURLs($url1, $url2, $strictness));
    }
    function test_checkURLs_invalid_2()
    {
        $strictness = SERVICES_TRACKBACK_STRICTNESS_MIDDLE;
        $url1 = "http:///trackback/index.php";
        $url2 = "http://www.example.net/trackback/index.php";
        $this->assertTrue(PEAR::isError(Services_Trackback::_checkURLs($url1, $url2, $strictness)));
    }
    function test_checkURLs_invalid_3()
    {
        // No real test, URLs are not invalid, but unequal
        $strictness = SERVICES_TRACKBACK_STRICTNESS_HIGH;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.htm";
        $this->assertTrue(PEAR::isError(Services_Trackback::_checkURLs($url1, $url2, $strictness)));
    }

    // 

    // }}}
    // {{{ Test _interpretTrackbackResponse()

    function test_interpretTrackbackResponse_success() {
        $xml = <<<EOD
<?xml version='1.0' encoding='iso-8859-1'?>
<response>
<error>0</error>
</response>
EOD;
        $res = Services_Trackback::_interpretTrackbackResponse($xml);
        $this->assertTrue($res);
    }
    
    function test_interpretTrackbackResponse_failure() {
        $xml = <<<EOD
<?xml version='1.0' encoding='iso-8859-1'?>
<response>
<error>-1</error>
<message>No more trackbacks from this host</message>
</response>
EOD;
        $res = Services_Trackback::_interpretTrackbackResponse($xml);
        $this->assertTrue(PEAR::isError($res));
    }
    
    function test_interpretTrackbackResponse_invalid_1() {
        $xml = <<<EOD
<?xml version='1.0' encoding='iso-8859-1'?>
<response>
<error></error>
<message>No more trackbacks from this host</message>
</response>
EOD;
        $res = Services_Trackback::_interpretTrackbackResponse($xml);
        $this->assertTrue(PEAR::isError($res));
    }
    
    function test_interpretTrackbackResponse_invalid_2() {
        $xml = <<<EOD
<?xml version='1.0' encoding='iso-8859-1'?>
<response>
</response>
EOD;

        $res = Services_Trackback::_interpretTrackbackResponse($xml);
        $this->assertTrue(PEAR::isError($res));
    }

    // }}}

}

$suite  = new PHPUnit_TestSuite("Webservices_Trackback_TestCase");
$result = PHPUnit::run($suite);

echo $result->toString();

?>
