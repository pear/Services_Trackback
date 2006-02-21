<?php

require_once 'PEAR/PackageFileManager2.php';

function dumpError($err) {
    var_dump($err);
    die();
}

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'dumpError');

$p2 = new PEAR_PackageFileManager2();
$p2->setOptions(
    array(
        'baseinstalldir'    => '/',
        'filelistgenerator' => 'cvs',
        'packagedirectory'  => dirname(__FILE__),
        'include'           => array(),
        'ignore'            => array(
            'package.xml',
            'package2.xml',
            '*.tgz',
            'generate*',
            'doc*',
        ),
        'dir_roles'         => array(
            'test'     => 'test',
        ),
        'simpleoutput'      => true,
    )
);

$p2->setPackage('Services_Trackback');
$p2->setSummary('Trackback - A generic class for sending and receiving trackbacks.');
$p2->setDescription('A generic class for sending and receiving trackbacks.');
$p2->setChannel('pear.php.net');

$p2->setPackageType('php');

$p2->generateContents();

$p2->setReleaseVersion('0.6.0');
$p2->setAPIVersion('0.6.0');
$p2->setReleaseStability('beta');
$p2->setAPIStability('beta');

$notes = <<<EOT
* New data added: 'extra' contains the content of the $_SERVER array now, when 
receiving a trackback.
* Option 'continuous' is spelled correctly now.
* The wordlist and regex filter do now decode HTML entities.
* New spam check, to check against Akismet.com web service.
* Fixed reference issues.
* More common spam words in the wordlist and regex spam checks.
EOT;

$p2->setNotes($notes);

$p2->addGlobalReplacement('package-info', '@package_version@', 'version');

$p2->addRelease();

$p2->addMaintainer('lead', 'toby', 'Tobias Schlitt', 'toby@php.net');

$p2->setPhpDep('4.3.0');
$p2->setPearinstallerDep('1.3.0');

$p2->setLicense('PHP License', 'http://www.php.net/license');

$p2->addDependencyGroup('autodiscover', 'Usage of Services_Trackback::autodiscover().');
$p2->addGroupPackageDepWithChannel('package', 'autodiscover', 'HTTP_Request', 'pear.php.net');

$p2->addDependencyGroup('akismet', 'Akismet.com spam checks.');
$p2->addGroupPackageDepWithChannel('package', 'akismet', 'HTTP_Request', 'pear.php.net');

$p2->addDependencyGroup('dnsbl', 'DNSBL/SURBL spam checks.');
$p2->addGroupPackageDepWithChannel('package', 'dnsbl', 'Net_DNSBL', 'pear.php.net');

$p1 =& $p2->exportCompatiblePackageFile1();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    echo "Writing package file\n";
    $p2->writePackageFile();
    $p1->writePackageFile();
} else {
    echo "Debugging package file\n";
    $p2->debugPackageFile();
    $p1->debugPackageFile();
}
?>
