<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck.
 *
 * This is the base class for Services_Trackback spamchecks. Since PHP4
 * lacks abstract class support, this class acts like a virtual abstract class. 
 * Each SpamCheck implementation has to extend this class and implement all of it's
 * abstract methods.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @abstract
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
    
    // }}}

/**
 * SpamProtection
 * Base class for Services_Trackback spam protection modules.
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
class Services_Trackback_SpamProtection {

    /**
     * Options for the spam check module. General and module specific.
     *
     * @var array
     * @access private
     */

    var $_options = array(
        'continuose'    => false,
        'sources'       => array(),
    );

    /**
     * Array of results, indexed analogue to the 'sources' option (boolean result value per source).
     *
     * @var array
     * @access private
     */

    var $_results = array();

    /**
     * Factory.
     * Create a new instance of a spam protection module.
     *
     * @since 0.5.0
     * @static
     * @access public
     * @param array $options An array of options for this spam protection module. General options are
     *                       'continuose':  Whether to continue checking more sources, if a match has been found.
     *                       'sources':     List of different sources for this module to check (eg. blacklist URLs,
     *                                      word arrays,...).
     *                       All further options depend on the specific module.
     * @return object(Services_Trackback_SpamCheck) The newly created SpamCheck object.
     */
    function create($options = null)
    {
        $this->_options = $options;
    }

    /**
     * Check for spam using this module.
     * This method is utilized by a Services_Trackback object to check for spam. Generally this method
     * may not be overwritten, but it can be, if necessary. This method calls the _checkSource() method
     * for each source defined in the $_options array (depending on the 'continuose' option), saves the 
     * results and returns the spam status determined by the check.
     *
     * @since 0.5.0
     * @access public
     * @return bool Whether the checked object is spam or not.
     */
    function check($trackback)
    {
        $spam = false;
        foreach ($this->_options['sources'] as $id => $source) {
            if ($spam && !$this->_options['continuose']) {
                // We already found spam and shall not continue
                $this->_results[$id] = false;
            } else {
                $this->_results[$id] = $this->_checkSource($this->_options['sources'][$id], $trackback);
            }
        }
        return $spam;
    }

    /**
     * Get spam check results.
     * Receive the results determined by the spam check.
     *
     * @since 0.5.0
     * @access public
     * @return array Array of specific spam check results.
     */
    function getResults()
    {
        return $this->_results;
    }
}

?>
