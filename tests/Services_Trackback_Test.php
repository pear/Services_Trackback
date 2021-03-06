<?php
require_once 'Services/Trackback.php';
require_once 'Services/Trackback/SpamCheck.php';
require_once 'Services/Trackback/SpamCheck/Mock.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__).'/trackback_data.php';

class Services_Trackback_Test extends PHPUnit_Framework_TestCase
{

    var $xml;

    function setUp() {
        $path = dirname(__FILE__) . '/data/Services_Trackback_Test/';

        $dir = new DirectoryIterator($path);
        $this->xml = array();
        foreach ($dir as $file) {
            @list($testName, $extension) = explode('.', (string)$file);

            if ($extension !== 'xml') {
                continue;
            }
            $full_path = $path . "/" . $file;

            $xml = file_get_contents($full_path);
            $this->xml[$testName] = trim($xml);
        }

        $this->trackback = new Services_Trackback();
    }

    function testCreate()
    {
        global $trackbackData;

        $options = array(
            'strictness'        => Services_Trackback::STRICTNESS_HIGH,
            'timeout'           => 10,
            'fetchlines'        => 100,
            'httprequest'       => array(
                'allowRedirects'    => false,
                'maxRedirects'      => 0,
                'useragent'         => 'Mozilla 10.0',
            ),
            'fetchextra' => true
        );

        $fakeTrack = new Services_Trackback;

        $fakeTrack->setOptions($options);
        $fakeTrack->setData($trackbackData['nospam']);

        $this->assertTrue(Services_Trackback::create($trackbackData['nospam'], $options) == $fakeTrack);
    }

    function testGetOptionsSetOptionsSuccess()
    {
        $options = array(
            'strictness'        => Services_Trackback::STRICTNESS_HIGH,
            'timeout'           => 10,
            'fetchlines'        => 100,
            'httprequest'       => array(
                'allowRedirects'    => false,
                'maxRedirects'      => 0,
                'useragent'         => 'Mozilla 10.0',
            ),
            'fetchextra' => false
        );

        $track = new Services_Trackback;

        $track->setOptions($options);

        $this->assertEquals($options, $track->getOptions());
    }


    function testAutodiscoverSuccess()
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

    function testAutodiscoverFailure()
    {
        $data = array(
            'id' => 'Test',
            'url' => 'http://pear.php.net/'
        );

        $track1 = Services_Trackback::create($data);
        try {
            $res    = $track1->autodiscover();
            $this->fail("Expected exception"); 
        } catch (Services_Trackback_Exception $ste) { }
    }

    function testSend()
    {
        global $trackbackData;
        $track = Services_Trackback::create($trackbackData['nospam']);
    }


    function testGetAutodiscoveryCodeNoComments()
    {
        global $trackbackData;
        $data = $trackbackData['nospam'];

        $xml = $this->xml['testGetAutodiscoveryCodeNoComments'];
        $xml = sprintf($xml, $data['url'], $data['url'], $data['title'], $data['trackback_url']);

        $track = Services_Trackback::create($data);

        $result = trim($track->getAutodiscoveryCode(false));

        $this->assertSame($xml, $result);
    }

    function testGetAutodiscoveryCodeComments()
    {
        global $trackbackData;
        $data = $trackbackData['nospam'];

        $xml = $this->xml['testGetAutodiscoveryCodeComments'];
        $xml = sprintf($xml, $data['url'], $data['url'], $data['title'], $data['trackback_url']);

        $track = Services_Trackback::create($data);

        $result = trim($track->getAutodiscoveryCode());

        $this->assertSame($xml, $result);
    }


    function testReceive()
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

        $fakeTrack = Services_Trackback::create($data);
        $fakeTrack->set('extra', $_SERVER);

        $this->assertTrue($recTrack == $fakeTrack);
    }


    function testGetResponseSuccess()
    {
        $xml = $this->xml['testGetResponseSuccess'];
        $this->assertTrue(!empty($xml), "Test was unable to locate sample data");

        $generated_response = $this->trackback->getResponseSuccess();
        $this->assertSame($xml, $generated_response);
    }

    function testGetResponseError()
    {
        $xml = $this->xml['testGetResponseError'];
        $this->assertTrue(!empty($xml), "Test was unable to locate sample data");

        $generated_error = $this->trackback->getResponseError('Me & you', -2);
        $this->assertSame($xml, $generated_error);
    }

    function testAddSpamCheckSuccess()
    {
        $spamCheck = new Services_Trackback_SpamCheck_Mock();

        $this->trackback->addSpamCheck($spamCheck);
    }

    function testAddSpamCheckFailure()
    {
        $spamCheck = new Services_Trackback();

        try {
            $this->trackback->addSpamCheck($spamCheck);
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testCreateSpamCheckSuccess()
    {
        global $trackbackData;
        $trackback = new Services_Trackback($trackbackData['nospam']);
        $spamCheck = Services_Trackback_SpamCheck::create('DNSBL');
        $this->assertTrue($trackback->createSpamCheck('DNSBL') == $spamCheck);
    }

    function testCreateSpamCheckFailure()
    {
        global $trackbackData;
        $trackback = new Services_Trackback($trackbackData['nospam']);

        try {
            $spamCheck = Services_Trackback_SpamCheck::create('DNS');
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}    }

    function testRemoveSpamCheckSuccess()
    {
        $spamCheck = new Services_Trackback_SpamCheck_Mock();
        $result = $this->trackback->addSpamCheck($spamCheck);
        
        $this->trackback->removeSpamCheck($spamCheck);
    }

    function testRemoveSpamCheckFailure()
    {
        $this->markTestIncomplete("This tests needs rewriting");
        $spamCheck = new Services_Trackback_SpamCheck();
        $this->trackback->addSpamCheck($spamCheck);
        $spamCheck2 = new Services_Trackback_SpamCheck();

        try {
            $this->trackback->removeSpamCheck($spamCheck2);
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testFromArray()
    {
        global $trackbackData;
        $fakeTrack = new Services_Trackback;
        $fakeTrack->setData($trackbackData['nospam']);
        $realTrack = new Services_Trackback;
        $realTrack->fromArray($trackbackData['nospam']);
        $this->assertTrue($realTrack == $fakeTrack);
    }

    function testGetContent()
    {
        $this->markTestSkipped("See Bug #13456");

        global $trackbackData;
        $trackback = Services_Trackback::create($trackbackData['nospam']);
        $url = 'http://schlitt.info/projects/PEAR/Services_Trackback/test_getContent.txt';
        $fakeRes = "Test text.\n";

        $res = $trackback->getContent($url);

        $this->assertTrue(trim($res) == trim($fakeRes));
    }

    function testGetEncodedData()
    {
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

        $this->assertTrue($this->trackback->getEncodedData(array('foo', 'bar', 'baz'), $in) == $out);
    }

    function testGetDecodedData()
    {
        $in = array(
            'foo' => 'bar & baz',
            'bar' => 'foo << baz',
            'baz' => 'foo && bar'
        );

        $out = array(
            'foo' => 'bar & baz',
            'baz' => 'foo && bar'
        );

        $this->assertTrue($this->trackback->getDecodedData(array('foo', 'baz'), $in) == $out);
    }

    function testCheckDataTrue()
    {
        $keys = array('id', 'test');
        $data = array('id' => 1, 'test' => 'x', 'test2' => 0);
        $this->assertTrue($this->trackback->checkData($keys, $data));
    }

    function testCheckDataFalse()
    {
        $keys = array('id', 'test');
        $data = array('id' => 1, 'test2' => 0);

        try {
            $this->trackback->checkData($keys, $data);
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }


    function testCheckURLsTrue1()
    {
        $strictness = Services_Trackback::STRICTNESS_LOW;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.net/trackbike/index.htm";
        $this->assertTrue(Services_Trackback::checkURLs($url1, $url2, $strictness));
    }

    function testCheckURLsTrue2()
    {
        $strictness = Services_Trackback::STRICTNESS_MIDDLE;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackbike/index.htm";
        $this->assertTrue($this->trackback->checkURLs($url1, $url2, $strictness));
    }

    function testCheckURLsTrue3()
    {
        $strictness = Services_Trackback::STRICTNESS_HIGH;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.php";
        $this->assertTrue($this->trackback->checkURLs($url1, $url2, $strictness));
    }


    function testCheckURLsFalse1()
    {
        // No real test, should always return true
        $strictness = Services_Trackback::STRICTNESS_LOW;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "https://test.net/trackbike/index.htm";
        $this->assertTrue($this->trackback->checkURLs($url1, $url2, $strictness));
    }

    function testCheckURLsFalse2()
    {
        $strictness = Services_Trackback::STRICTNESS_MIDDLE;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.net/trackback/index.php";

        try {
            $this->trackback->checkURLs($url1, $url2, $strictness);
            
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testCheckURLsFalse3()
    {
        $strictness = Services_Trackback::STRICTNESS_HIGH;
        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.htm";
        try {
            $this->trackback->checkURLs($url1, $url2, $strictness);
            
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testCheckURLsInvalid1()
    {
        // No real test, should always return true
        $strictness = Services_Trackback::STRICTNESS_LOW;

        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "https://test.net/trackbike/index.htm";

        $this->assertTrue($this->trackback->checkURLs($url1, $url2, $strictness));
    }

    function testCheckURLsInvalid2()
    {
        $strictness = Services_Trackback::STRICTNESS_MIDDLE;

        $url1 = "http:///trackback/index.php";
        $url2 = "http://www.example.net/trackback/index.php";

        try {
            $this->trackback->checkURLs($url1, $url2, $strictness);
            
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testCheckURLsInvalid3()
    {
        // No real test, URLs are not invalid, but unequal
        $strictness = Services_Trackback::STRICTNESS_HIGH;

        $url1 = "http://www.example.com/trackback/index.php";
        $url2 = "http://www.example.com/trackback/index.htm";

        try {
            $this->trackback->checkURLs($url1, $url2, $strictness);
            
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) {}
    }

    function testInterpretTrackbackResponseSuccess()
    {
        $xml = $this->xml['testInterpretTrackbackResponseSuccess'];
        $res = $this->trackback->interpretTrackbackResponse($xml);
        $this->assertTrue($res);
    }

    function testInterpretTrackbackResponseFailure()
    {
        $xml = $this->xml['testInterpretTrackbackResponseFailure'];
        try {
            $res = $this->trackback->interpretTrackbackResponse($xml);
            $this->fail("Expected exception");
        } catch (Services_Trackback_Exception $ste) { }
    }

    function testInterpretTrackbackResponseInvalid1()
    {
        $xml = $this->xml['testInterpretTrackbackResponseInvalid1'];
        try {
            $res = $this->trackback->interpretTrackbackResponse($xml);
            $this->fail("Expected exception"); 
        } catch (Services_Trackback_Exception $ste) { }
    }

    function testInterpretTrackbackResponseInvalid2()
    {
        $xml = $this->xml['testInterpretTrackbackResponseInvalid2'];

        try {
            $res = $this->trackback->interpretTrackbackResponse($xml);
            $this->fail("Expected exception"); 
        } catch (Services_Trackback_Exception $ste) { }
    }
}
