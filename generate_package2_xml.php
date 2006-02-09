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
            'tests'     => 'test',
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

$p2->setReleaseVersion('0.5.1');
$p2->setAPIVersion('0.5.0');
$p2->setReleaseStability('alpha');
$p2->setAPIStability('alpha');

$notes = <<<EOT
* Fixed Bug #5667: HTTP_Request::getRequestCode( ) - no such function for the latest HTTP_Request.
* Fixed Bug #6341: Undefined variable on line 315 & 378.
* Fixed reference issues.
* Fixed small issue in continuous spam checks.
* Fix PEAR_Error issue in test cases.
* Added development environment include_path settings to test runner.
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
