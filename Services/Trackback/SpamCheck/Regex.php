<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_Regex.
 *
 * This spam detection module for Services_Trackback searches a given trackback
 * for word matches.
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
 * Load SpamCheck base class
 */

require_once 'Services/Trackback/SpamCheck.php';

/**
 * Regex
 * Module for spam detecion using perl compatible regular expressions.
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
class Services_Trackback_SpamCheck_Regex extends Services_Trackback_SpamCheck
{

    /**
     * Options for the Regex.
     *
     * @var array
     * @since 0.5.0
     */
    protected $options = array(
        'continuous'    => false,
        'sources'       => array(
            '(acne|adipex|anal|blackjack|cash|casino|cigar|closet|daystore|drugs|erection|fundslender|gambling|hire|hydrocodone|investing|lasik|loan|mattress|mortgage|naproxen|neurontin|payday|penis|pharma|phentermine|poker|porn|rheuma|roulette|sadism|sex|smoking|texas hold|tramadol|uxury|viagra|vioxx|weight loss|xanax|zantac)',
        ),
        'elements'      => array(
            'title',
            'excerpt',
            'blog_name',
            'url',
        ),
        'delimiter'     => '@',
        'modifier'     => 'i',
        'minmatches'    => 1,
    );

    /**
     * Constructor.
     * Create a new instance of the DNSBL spam protection module.
     *
     * @param array $options An array of options for this spam protection module.
     *                      General options are
     *                       'continuous':  Whether to continue checking more sources
     *                                      if a match has been found.
     *                       'sources':     List of blacklist nameservers. Indexed.
     *                       'comparefunc': A compare function callback with
     *                                      parameters $haystack, $needle
     *                                      (like 'stripos').
     *                       'minmatches':  How many words have to be
     *                                      found to consider spam.
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
    }

    /**
     * Check for spam using this module.
     * This method is utilized by a Services_Trackback object to check for spam.
     * Generally this method may not be overwritten, but it can be, if necessary.
     * This method calls the _checkSource() method for each source defined in the
     * $options array (depending on the 'continuous' option), saves the
     * results and returns the spam status determined by the check.
     *
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @return bool Whether the checked object is spam or not.
     */
    public function check($trackback)
    {
        $spamCount = 0;
        foreach (array_keys($this->options['sources']) as $id) {
            if ($spamCount >= $this->options['minmatches']
                && !$this->options['continuous']) {
                // We already found spam and shall not continue
                $this->results[$id] = false;
            } else {
                $res = $this->checkSource($this->options['sources'][$id],
                                           $trackback);

                $spamCount += ($res === true) ? 1 : 0;

                $this->results[$id] = $res;
            }
        }
        return ($spamCount >= $this->options['minmatches']);
    }

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @return bool True if trackback is spam
     */
    function checkSource($source, $trackback)
    {
        $del  = $this->options['delimiter'];
        $mod  = $this->options['modifier'];
        $spam = false;

        foreach ($this->options['elements'] as $element) {
            $elements[$element] = html_entity_decode($trackback->get($element));
        }

        foreach ($elements as $element) {
            if (0 !== preg_match($del.$source.$del.$mod, $element)) {
                $spam = true;
                break;
            }
        }

        return $spam;
    }

    /**
     * Checks source text for an element, regardless of case.
     *
     * @param string $source  Source text
     * @param string $element Element text
     *
     * @return int
     */
    function _stripos($source, $element)
    {
        return strpos(strtolower($element), strtolower($source));
    }

}
