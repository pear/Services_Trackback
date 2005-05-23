#!/usr/bin/php
<?php

    $make = 1;
	require_once('PEAR/PackageFileManager.php');

	$pkg = new PEAR_PackageFileManager;

	// directory that PEAR CVS is located in
	$cvsdir  = '/cvs/pear/';
	$packagedir = $cvsdir . 'Services_Trackback/';
	
	// Filemanager settings
	$category = 'Services';
	$package = 'Services_Trackback';
	
	$version = '0.5.0';
	$state = 'alpha';
	
	$summary = 'Trackback - A generic class for sending and receiving trackbacks.';
	$description = <<<EOT
A generic class for sending and receiving trackbacks.
EOT;

	$notes = <<<EOT
* New API to check if a trackback is spam.
* Implemented spam checks using Wordlist, Regex, DNSBL, SURBL.
* Completed unit tests.
* Refined overall API.
EOT;
	
	$e = $pkg->setOptions(
		array('simpleoutput'      => true,
		      'baseinstalldir'    => '',
		      'summary'           => $summary,
		      'description'       => $description,
		      'version'           => $version,
	          'packagedirectory'  => $packagedir,
	          'pathtopackagefile' => $packagedir,
              'state'             => $state,
              'filelistgenerator' => 'cvs',
              'notes'             => $notes,
			  'package'           => $package,
			  'dir_roles' => array(
			  		'test' => 'test'),
		      'ignore' => array('package.xml',
                                'package2.xml',
                                'CHANGES.txt',
		                        'doc*', 
		                        'generate_package_xml.php',
		                        '*.tgz',
                                'local_test.php'),
	));
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}
	
	$e = $pkg->addMaintainer('toby', 'lead', 'Tobias Schlitt', 'toby@php.net');
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}

    $e = $pkg->addDependency('HTTP_Request', '1.2.4', 'ge', 'pkg', true);
    $e = $pkg->addDependency('Net_DNSBL', '1.0.0', 'ge', 'pkg', true);

    $e = $pkg->addGlobalReplacement('package-info', '@package_version@', 'version');;

	if (PEAR::isError($e)) {
    	echo $e->getMessage();
    	exit;
	}

	// hack until they get their shit in line with docroot role
	$pkg->addRole('tpl', 'php');
	$pkg->addRole('png', 'php');
	$pkg->addRole('gif', 'php');
	$pkg->addRole('jpg', 'php');
	$pkg->addRole('css', 'php');
	$pkg->addRole('js', 'php');
	$pkg->addRole('ini', 'php');
	$pkg->addRole('inc', 'php');
	$pkg->addRole('afm', 'php');
	$pkg->addRole('pkg', 'doc');
	$pkg->addRole('cls', 'doc');
	$pkg->addRole('proc', 'doc');
	$pkg->addRole('sh', 'doc');
	
	if (isset($make)) {
    	$e = $pkg->writePackageFile();
	} else {
    	$e = $pkg->debugPackageFile();
	}
	
	if (PEAR::isError($e)) {
    	echo $e->getMessage();
	}
	
	if (!isset($make)) {
    	echo '<a href="' . $_SERVER['PHP_SELF'] . '?make=1">Make this file</a>';
	}
?>
