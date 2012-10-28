<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_DNSBL.
 *
 * This spam detection module for Services_Trackback utilizes DNS
 * blacklists for detection of hosts used for spamming.
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
 * Load Net_DNSBL for spam cheching
 */
require_once 'Net/DNSBL.php';

/**
 * Load SpamCheck base class
 */
require_once 'Services/Trackback/SpamCheck.php';

/**
 * DNSBL
 * Module for spam detecion using DNSBL.
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
class Services_Trackback_SpamCheck_DNSBL extends Services_Trackback_SpamCheck
{

    /**
     * Options for the SpamProtection.
     *
     * @var array
     * @since 0.5.0
     */
    protected $options = array(
        'continuous'    => false,
        'sources'       => array(
            'bl.spamcop.net'
        ),
    );

    /**
     * The Net_DNSBL object for checking.
     *
     * @var object(Net_DNSBL)
     * @since 0.5.0
     */
    protected $dnsbl;

    /**
     * Constructor.
     * Create a new instance of the DNSBL spam protection module.
     *
     * @param array $options An array of options for this spam protection module.
     *                      General options are
     *                       'continuous':  Whether to continue checking more sources
     *                                      if a match has been found.
     *                       'sources':     List of blacklist nameservers. Indexed.
     *
     * @since 0.5.0
     *
     * @return void
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            foreach ($options as $key => $val) {
                $this->options[$key] = $val;
            }
        }
        $this->dnsbl = new Net_DNSBL();
    }

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the _sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @return bool True if trackback is spam, false, if not. 
     */
    function checkSource($source, $trackback)
    {
        $this->dnsbl->setBlacklists(array($source));
        return $this->dnsbl->isListed($trackback->get('host'));
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

        //This should really call Net_DNSBL::reset() or similar, which doesn't exist
        $this->dnsbl = new Net_DNSBL();
    }
}
