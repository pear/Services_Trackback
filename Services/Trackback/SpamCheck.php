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
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @abstract
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Trackback
 * @since     File available since Release 0.5.0
 */

require_once 'Services/Trackback/Exception.php';

/**
 * SpamCheck
 * Base class for Services_Trackback spam protection modules.
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
class Services_Trackback_SpamCheck
{

    /**
     * Options for the spam check module. General and module specific.
     *
     * @var array
     * @since 0.5.0
     */
    protected $options = array(
        'continuous'    => false,
        'sources'       => array(),
    );

    /**
     * Array of results, indexed analogue to the 'sources'
     * option (boolean result value per source).
     *
     * @var array
     */
    protected $results = array();

    /**
     * Factory.
     * Create a new instance of a spam protection module.
     *
     * @param string $type    Name of a SpamCheck driver
     * @param array  $options An array of options for this spam protection module.
     *                        General options are
     *                         'continuous':  Whether to continue checking more
     *                                        sources if a match has been found.
     *                         'sources':     List of blacklist nameservers. Indexed
     *
     * @since 0.5.0
     * @static
     * @return Services_Trackback_SpamCheck The newly created SpamCheck object.
     */
    public static function create($type, $options = null)
    {
        $filename     = 'Services/Trackback/SpamCheck/' . $type . '.php';
        $filepathes[] = dirname(__FILE__).'/SpamCheck/'.$type.'.php';
        $filepathes[] = dirname(__FILE__).'/'.$type.'.php';

        $classname = 'Services_Trackback_SpamCheck_' . $type;

        // Check if class already exists or is includeable
        if (!class_exists($classname)) {
            if (file_exists($filepathes[0]) || file_exists($filepathes[1])) {
                include_once $filename;
            }
        }

        // We now definitly have to have the class available else the spam check
        // contained errors / is unavailable.
        if (!class_exists($classname)) {
            throw new Services_Trackback_Exception('SpamCheck ' . $type . ' not found.');
        }
        $res = new $classname(@$options);
        return $res;
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
        $this->reset();
        $spam = false;
        foreach (array_keys($this->options['sources']) as $id) {
            if ($spam && !$this->options['continuous']) {
                // We already found spam and shall not continue
                $this->results[$id] = false;
                break;
            } else {
                $result = $this->checkSource($this->options['sources'][$id],
                                              $trackback);

                $this->results[$id] = $result;

                $spam = ($spam || $this->results[$id]);
            }
        }
        return $spam;
    }

    /**
     * Get spam check results.
     * Receive the results determined by the spam check.
     *
     * @since 0.5.0
     * @return array Array of specific spam check results.
     */
    public function getResults()
    {
        return $this->results;
    }

    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Reset results.
     * Reset results to reuse SpamCheck.
     *
     * @since 0.5.0
     * @return null
     */
    public function reset()
    {
        $this->results = array();
    }

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the _sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @abstract
     * @return bool True if trackback is spam, false, if not, Services_Trackback_Exception on error.
     */
    protected function checkSource($source, $trackback)
    {
        throw new Services_Trackback_Exception('Method not implemented.', -1);
    }

}
