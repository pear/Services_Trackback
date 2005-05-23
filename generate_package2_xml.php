<?php

require_once 'PEAR/PackageFile2Manager.php';

function dumpError($err) {
    var_dump($err);
    die();
}

// PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'dumpError');

$p2 = new PEAR_PackageFile2Manager;
$p2->setOptions(array(
        'filelistgenerator' => 'cvs',
        'packagedirectory' => dirname(__FILE__),
        'baseinstalldir' => '',
        'include' => array(),
        'ignore'  => array(
            'package.xml',
            'package2.xml',
            '*.tgz',
            'generate*',
            
        ),
        'dir_roles' => array(
            'test' => 'test'
        ),
        'simpleoutput' => true
        )
);

$p2->setPackageType('php');

$p2->addRelease();

$p2->generateContents();

$p2->setPackage('Services_Trackback');

$p2->setChannel('pear.php.net');

$p2->setReleaseVersion('0.5.0');

$p2->setAPIVersion('0.5.0');

$p2->setReleaseStability('alpha');

$p2->setAPIStability('alpha');

$p2->setSummary('Trackback - A generic class for sending and receiving trackbacks.');

$p2->setDescription('A generic class for sending and receiving trackbacks.');

$p2->setNotes('* New API to check if a trackback is spam.
* Implemented spam checks using Wordlist, Regex, DNSBL, SURBL.
* Completed unit tests.
* Refined overall API.');

$p2->setPhpDep('4.3.0');
$p2->setPearinstallerDep('1.3.0');


$p2->addMaintainer('lead', 'toby', 'Tobias Schlitt', 'toby@php.net');

$p2->setLicense('PHP License', 'http://www.php.net/license');

$p2->addGlobalReplacement('package-info', '@package_version@', 'version');

$p2->addDependencyGroup('autodiscover', 'Usage of Services_Trackback::autodiscover()');
$p2->addDependencyGroup('dnsbl', 'DNSBL/SURBL spam checks');;

$p2->addGroupPackageDepWithChannel('package', 'autodiscover', 'HTTP_Request', 'pear.php.net');

$p2->addGroupPackageDepWithChannel('package', 'dnsbl', 'Net_DNSBL', 'pear.php.net');

$p1 = &$p2->exportCompatiblePackageFile1();
if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    $p1->writePackageFile();
    $p2->writePackageFile();
} else {
    $p1->debugPackageFile();
    $p2->debugPackageFile();
}
?>
