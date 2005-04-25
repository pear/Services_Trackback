<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_Wordlist.
 *
 * This spam detection module for Services_Trackback searches a given trackback
 * for word matches.
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
 * Wordlist
 * Module for spam detecion using a word list.
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
class Services_Trackback_SpamProtection_Wordlist extends Services_Trackback_SpamProtection {

    // {{{ _options
    
    /**
     * Options for the Wordlist.
     *
     * @var array
     * @since 0.5.0
     * @access protected
     */
    var $_options = array(
        'continuose'    => false,
        'sources'       => array(
            'porn',
            'sex',
            'viagra',
            'erection',
            'anal',
            'gambling',
            'poker',
            'casino',
            'pharmacy',
            'drugs',
            'adipex',
            'naproxen',
            'xanax',
            'phentermine',
            'diet',
            'smoking',
            'rheuma',
            'roulette',
            'payday',
            'loan',
        ),
        'elements'      => array(
            'title',
            'excerpt',
        ),
        'comparefunc' => 'stripos',
        'minmatches'    => 1,
    );

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
     *                       'comparefunc': A compare function callback with parameters $haystack, $needle (like 'stripos').
     *                       'minmatches':  How many words have to be found to consider spam.
     * @return object(Services_Trackback_SpamCheck_SURBL) The newly created SpamCheck object.
     */
    function create($options = null)
    {
        $this->_options = $options;
    }
    
    // }}}
    // {{{ check()

    function check($trackback)
    {
        $spamCount = 0;
        foreach ($this->_options['sources'] as $id => $source) {
            if ($spam > 0 && !$this->_options['continuose']) {
                // We already found spam and shall not continue
                $this->_results[$id] = false;
            } else {
                $this->_results[$id] = $this->_checkSource($this->_options['sources'][$id], $trackback);
            }
        }
        return ($spamCount >= $this->_options['minmatches']);
    }

    // }}}
    // {{{ _checkSource()
    
    function _checkSource(&$source, $trackback)
    {
        $spam = false;
        foreach ($this->_options['elements'] as $element) {
            if (false !== $this->_options['comparefunc']($source, $trackback->get($element)) {
                $spam = true;
                break;
            }
        }
        return $spam;
    }

    // }}}
    
}
