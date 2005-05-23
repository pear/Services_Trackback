<?php

set_include_path('.:/cvs/pear/Services_Trackback:/usr/php4/share/pear');

$conf = array(
    'online' => true,
);

// {{{ Var_Dump

// For more readable dumping of variables
require_once 'Var_Dump.php';

if (isset($_SERVER['REQUEST_URI'])) {
    // Setup for displaying XHTML output.
    Var_Dump::displayInit(
        array('display_mode'=>'XHTML_Text'), 
        array(
            'mode'          => 'wide',
            'offset'        => 4,
            'after_text'    => '<br />',
        )
    );
    // Headline function for XHTML output.
    function head ( $text ) {
        echo '<br /><b>'.$text.'</b><br />';
    }
} else {
    // Setup for displaying console output.
    Var_Dump::displayInit(
        // Choose text mode
        array('display_mode'    =>  'Text'),
        // Rendere options
        array(
            'mode'              =>  'wide',
            'before_text'       =>  "\n",
            'after_text'        =>  "\n",
        )
    );
    // Headline function for Text output.
    function head ( $text ) {
        echo "\n\n--- ".$text." ---\n\n";
    }
}

// }}}

require_once 'Services/Trackback.php';
require_once 'test/trackback_data.php';

$trackback = new Services_Trackback($trackbackData['nospam']);

head('Success response');
Var_Dump::display($trackback->getResponseSuccess());

head('Error response');
Var_Dump::display($trackback->getResponseError(1, "Test error"));

unset($trackback);

$trackback = Services_Trackback::create($trackbackData['nospam']);

head('Newly created trackback object');

Var_Dump::display($trackback);

head('Autodiscovery code (with comments)');
Var_Dump::display($trackback->getAutoDiscoveryCode());

head('Autodiscovery code (without comments)');
Var_Dump::display($trackback->getAutoDiscoveryCode(false));

unset($trackback);

$trackback = Services_Trackback::create($trackbackData['nospam']);
Var_Dump::display($trackback->receive($trackbackData['undetected']));

head('Received trackback object');
Var_Dump::display($trackback);

unset($trackback);

if (true === $conf['online']) {
    head('Autodiscovered trackback object');

    $trackback = Services_Trackback::create(array('id' => 'Test', 'url' => 'http://pear.php.net/package/Net_FTP'));
    Var_Dump::display($trackback->autodiscover());
    Var_Dump::display($trackback);

    head('Sending trackback');

    $trackback->set('title', 'Testing Services_Trackback');
    $trackback->set('url', 'http://www.example.com');
    $trackback->set('excerpt', 'Test test tes...');
    $trackback->set('blog_name', 'Tobias Schlitt testing Services_Trackback');

    Var_Dump::display($trackback);
    Var_Dump::display($trackback->send());

    unset($trackback);

	head('Wordlist spam check');
	
	foreach ($trackbackData as $id =>  $set) {
	    echo "\n\n-- $id --";
	    $trackback = Services_Trackback::create($set);
	    $trackback->createSpamCheck('Wordlist');
	    Var_Dump::display($trackback->checkSpam());
	    // Var_Dump::display($trackback);
	}
	
	head('DNSBL spam check');
	
	foreach ($trackbackData as $id =>  $set) {
	    echo "\n\n-- $id --";
	    $trackback = Services_Trackback::create($set);
	    $trackback->createSpamCheck('DNSBL');
	    Var_Dump::display($trackback->checkSpam());
	    // Var_Dump::display($trackback);
	}
	
	head('SURBL spam check');
	
	foreach ($trackbackData as $id =>  $set) {
	    echo "\n\n-- $id --";
	    $trackback = Services_Trackback::create($set);
	    $trackback->createSpamCheck('SURBL');
	    Var_Dump::display($trackback->checkSpam());
	    // Var_Dump::display($trackback);
	}
	
    head('Wordlist + DNSBL spam check');
	
	foreach ($trackbackData as $id =>  $set) {
	    echo "\n\n-- $id --";
	    $trackback = Services_Trackback::create($set);
	    $trackback->createSpamCheck('Wordlist');
	    $trackback->createSpamCheck('DNSBL');
	    Var_Dump::display($trackback->checkSpam());
	    // Var_Dump::display($trackback);
	}

}
    	
?>
