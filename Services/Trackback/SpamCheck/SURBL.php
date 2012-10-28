<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_SURBL.
 *
 * This spam detection module for Services_Trackback utilizes SUR
 * blacklists for detection of URLs used in spam.
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
 * @since     File available since Release 0.5.0
 */

/**
 * Load SpamCheck base.
 */
require_once 'Services/Trackback/SpamCheck.php';

/**
 * Load Net_SURBL for spam cheching
 */
require_once 'Net/DNSBL/SURBL.php';

/**
 * SURBL
 * Module for spam detecion using SURBL.
 *
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Services_Trackback
 * @since     0.5.0
 */
class Services_Trackback_SpamCheck_SURBL extends Services_Trackback_SpamCheck
{

    /**
     * Options for the SpamCheck.
     *
     * @var array
     * @since 0.5.0
     */
    protected $options = array(
        'continuous'    => false,
        'sources'       => array(
            'multi.surbl.org'
        ),
        'elements'      => array(
            'url',
            'title',
            'excerpt',
        ),
    );

    /**
     * The Net_DNSBL_SURBL object for checking.
     *
     * @var object(Net_DNSBL_SURBL)
     * @since 0.5.0
     */
    protected $surbl;

    /**
     * URLs extracted from the trackback.
     *
     * @var array
     * @since 0.5.0
     */
    protected $urls = array();

    /**
     * Constructor.
     * Create a new instance of the SURBL spam protection module.
     *
     * @param array $options An array of options for this spam protection module.
     *                       General options are
     *                       'continuous':  Whether to continue checking more
     *                                      sources, if a match has been found.
     *                       'sources':     List of blacklist servers. Indexed.
     *                       'elements'     Array of trackback data fields
     *                                      extract URLs from (standard is 'title'
     *                                      and 'excerpt').
     *
     * @since 0.5.0
     * @return Services_Trackback_SpamCheck_SURBL The newly created SpamCheck object.
     */
    function __construct($options = null)
    {
        if (is_array($options)) {
            foreach ($options as $key => $val) {
                $this->options[$key] = $val;
            }
        }
        $this->surbl = new Net_DNSBL_SURBL();
    }

    /**
     * Reset results.
     * Reset results to reuse SpamCheck.
     *
     * @since 0.5.0
     * @return null
     */
    function reset()
    {
        parent::reset();
        $this->urls  = array();
        $this->surbl = new Net_DNSBL_SURBL();
    }

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the _sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @abstract
     * @return bool True if trackback is spam.
     */
    function checkSource($source, $trackback)
    {
        if (count($this->urls) == 0) {
            $this->extractURLs($trackback);
        }
        $this->surbl->setBlacklists(array($source));
        $spam = false;
        foreach ($this->urls as $url) {
            $spam = ($spam || $this->surbl->isListed($url));
            if ($spam) {
                break;
            }
        }
        return $spam;
    }

    /**
     * Extract all URLS from the Trackback
     *
     * @param Services_Trackback $trackback The trackback to extract urls from.
     *
     * @return void
     */
    function extractURLs($trackback)
    {
        $matches = array();

        $urls = '(?:http|file|ftp)';
        $ltrs = 'a-z0-9';
        $gunk = '.-';
        $punc = $gunk;
        $any  = "$ltrs$gunk";

        $regex = "{
                      $urls   ://
                      [$any]+


                      (?=
                        [$punc]*
                        [^$any]
                      |
                      )
                  }x";
        foreach ($this->options['elements'] as $element) {
            if (0 !== preg_match_all($regex, $trackback->get($element), $matches)) {
                foreach ($matches[0] as $match) {
                    $this->urls[md5($match)] = $match;
                }
            }
        }
    }

}
