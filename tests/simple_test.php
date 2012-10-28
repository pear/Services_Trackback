<?php
/**
 * Services_Trackback.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Trackback
 */

$conf = array(
    'online' => true,
);

// For more readable dumping of variables
require_once 'Var_Dump.php';

if (isset($_SERVER['REQUEST_URI'])) {
    // Setup for displaying XHTML output.
    Var_Dump::displayInit(array('display_mode'=>'XHTML_Text'),
                          array(
                              'mode'          => 'wide',
                              'offset'        => 4,
                              'after_text'    => '<br />',
                          ));

    /**
     * Headline function for XHTML output.
     *
     * @param string $text Text to render
     *
     * @return void
     */
    function head($text)
    {
        echo '<br /><b>'.$text.'</b><br />';
    }
} else {
    // Setup for displaying console output.
    Var_Dump::displayInit(array('display_mode'    =>  'Text'),
                        // Rendere options
                        array(
                            'mode'              =>  'wide',
                            'before_text'       =>  "\n",
                            'after_text'        =>  "\n",
                        ));
    /**
     * Headline function for Text output.
     *
     * @param string $text Text to render
     *
     * @return void
     */
    function head($text)
    {
        echo "\n\n--- ".$text." ---\n\n";
    }
}


require_once 'Services/Trackback.php';
require_once dirname(__FILE__).'/trackback_data.php';

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

    $data = array('id' => 'Test', 'url' => 'http://pear.php.net/package/Net_FTP');

    $trackback = Services_Trackback::create($data);
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
