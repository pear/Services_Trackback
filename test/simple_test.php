<?php

set_include_path('.:/cvs/pear/Services_Trackback:/usr/php4/share/pear');

require_once 'Services/Trackback.php';

$trackback = new Services_Trackback(array('id' => 1));

echo "\n\n--------------- Success response ---------------\n\n";
echo $trackback->getResponseSuccess();

echo "\n\n--------------- Error response ---------------\n\n";
echo $trackback->getResponseError(1, "Test error");

$trackback = Services_Trackback::create(array('id' => 1, 'title' => 'Test title', 'url' => 'http://www.example.com/', 'trackback_url' => 'http://www.example.com/trackback/1'));

echo "\n\n--------------- Newly created trackback object ---------------\n\n";

var_dump($trackback);

echo "\n\n--------------- Autodiscovery code (with comments) ---------------\n\n";
var_dump($trackback->getAutoDiscoveryCode());

echo "\n\n--------------- Autodiscovery code (without comments) ---------------\n\n";
var_dump($trackback->getAutoDiscoveryCode(false));

$data = array(
    'title'     => 'Test title',
    'excerpt'   => 'A test blog entry',
    'url'       => 'http://www.example.com/',
    'blog_name' => 'Example weblog'
);

$trackback = Services_Trackback::create(array('id' => 1));
var_dump($trackback->receive($data));

echo "\n\n--------------- Received trackback object ---------------\n\n";
var_dump($trackback);


echo "\n\n--------------- Autodiscovered trackback object ---------------\n\n";

$trackback = Services_Trackback::create(array('id' => 'Test', 'url' => 'http://pear.php.net/package/Net_FTP'));
var_dump($trackback->autodiscover());

var_dump($trackback);

echo "\n\n--------------- Sending trackback ---------------\n\n";

$trackback->set('title', 'Testing Services_Trackback');
$trackback->set('url', 'http://www.example.com');
$trackback->set('excerpt', 'Test test tes...');
$trackback->set('blog_name', 'Tobias Schlitt testing Services_Trackback');

var_dump($trackback);
var_dump($trackback->send());

?>
