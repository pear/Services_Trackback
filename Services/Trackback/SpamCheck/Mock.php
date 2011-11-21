<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_Mock.
 *
 * A non functional spam check module for unit test purposes
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

    // {{{ require_once

/**
 * Load PEAR error handling
 */
require_once 'PEAR.php';

/**
 * Load SpamCheck base class
 */

require_once 'Services/Trackback/SpamCheck.php';

    // }}}

/**
 * Mock
 *
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Services_Trackback
 * @since     0.5.0
 * @access    public
 */
class Services_Trackback_SpamCheck_Mock extends Services_Trackback_SpamCheck
{

    // {{{ Services_Trackback_SpamCheck_Mock()

    /**
     * Constructor.
     * Create a new instance of the Mock spam protection module.
     *
     * @param array $options An array of options for this spam protection module.
     *                        General options are
     *                         'found_spam':  Boolean. Default is true. 
     *                                        Act like we found spam or not.
     *
     * @since 0.5.0
     * @access public
     * @return Services_Trackback_SpamCheck_WordList The newly created object.
     */
    function __construct($options = null)
    {
        //By default, we find spam
        $this->_options['found_spam'] = true;

        if (is_array($options)) {
            foreach ($options as $key => $val) {
                $this->_options[$key] = $val;
            }
        }
    }

    // }}}
    // {{{ check()

    /**
     * Check for spam using this module.
     *
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @access public
     * @return bool Whether the checked object is spam or not.
     */
    function check($trackback)
    {
        return !empty($this->_options['found_spam']);
    }

    // }}}
}
