<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_SURBL.
 *
 * This spam detection module for Services_Trackback utilizes SUR
 * blacklists for detection of URLs used in spam.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Webservices
 * @package    Trackback
 * @author     Tobias Schlitt <toby@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Services_Trackback
 * @since      File available since Release 0.5.0
 */
    
    // {{{ require_once

/**
 * Load PEAR error handling
 */
require_once 'PEAR.php';
   
/**
 * Load Net_SURBL for spam cheching
 */
require_once 'Net/SURBL.php';
   
    // }}}

/**
 * SURBL
 * Module for spam detecion using SURBL.
 *
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @category   Webservices
 * @package    Trackback
 * @author     Tobias Schlitt <toby@php.net>
 * @copyright  1997-2005 The PHP Group
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Services_Trackback
 * @since      0.5.0
 * @access     public
 */
class Services_Trackback_SpamProtection_SURBL extends Services_Trackback_SpamProtection {

    // {{{ _options
    
    /**
     * Options for the SpamProtection.
     *
     * @var array
     * @since 0.5.0
     * @access protected
     */
    var $_options = array(
        'continuose'    => false,
        'sources'       => array(
            'multi.surbl.org'
        ),
        'elements'      => array(
            'title',
            'excerpt',
        ),
    );

    // }}}
    // {{{ _surbl
    
    /**
     * The Net_DNSBL_SURBL object for checking.
     *
     * @var object(Net_DNSBL_SURBL)
     * @since 0.5.0
     * @access protected
     */
    var $_surbl;

    // }}}
    // {{{ _urls
    
    /**
     * URLs extracted from the trackback.
     *
     * @var array
     * @access private
     * @since 0.5.0
     */
    var $_urls;

    // }}}
    // {{{ create()
    
    /**
     * Factory.
     * Create a new instance of the SURBL spam protection module.
     *
     * @since 0.5.0
     * @static
     * @access public
     * @param array $options An array of options for this spam protection module. General options are
     *                       'continuose':  Whether to continue checking more sources, if a match has been found.
     *                       'sources':     List of blacklist servers. Indexed.
     *                       'elements'     Array of trackback data fields extract URLs from (standard is 'title' 
     *                                      and 'excerpt').
     * @return object(Services_Trackback_SpamCheck_SURBL) The newly created SpamCheck object.
     */
    function create($options = null)
    {
        $this->_options = $options;
        $this->_dnsbl = new Net_DNSBL_SURBL();
    }

    // }}}
    // {{{ _checkSource()

    function _checkSource(&$source, $trackback)
    {
        if (!isset($this->_urls)) {
            $this->_extractURLs($trackback);
        }
        $this->_dnsbl->setBlacklists(array($source));
        $spam = false;
        foreach ($this->_urls as $url) {
            $spam = ($spam || $this->_dnsbl->isListed($url));
            if ($spam) {
                break;
            }
        }
        return $spam;
    }

    // }}}
    
}
