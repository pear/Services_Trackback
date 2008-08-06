<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback_SpamCheck_Akismet.
 *
 * This spam detection module for Services_Trackback checks the given trackback
 * against the spam checking webservice Akismet {@link http://akismet.com/}
 * provided by {@link http://wordpress.com}. ATTENTION: To use this spam check,
 * you need a valid WordPress.com API key.
 *
 * PHP versions 4 and 5
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
 * @since     File available since Release 0.6.0
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

/**
 * HTTP_Request for sending POST requests to Akismet
 */
require_once 'HTTP/Request.php';

    // }}}

/**
 * Akismet
 * Module for spam detecion using {@link http://akismet.com}.
 *
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Services_Trackback
 * @since     0.6.0
 * @access    public
 */
class Services_Trackback_SpamCheck_Akismet extends Services_Trackback_SpamCheck
{

    // {{{ _options

    /**
     * Options for the Wordlist.
     *
     * @var array
     * @since 0.5.0
     * @access protected
     */
    var $_options = array('continuous'    => false,
                          'sources'       => array('rest.akismet.com/1.1/'),

                          // URL of the blog sending the Akismet request
                          'url'           => '',

                          // WordPress.com API key to use
                          'key'           => '',
                          'elements'      => array('title',
                                                   'excerpt',
                                                   'blog_name',
                                                   'url',
                                                   'host'));

    // }}}
    // {{{ Services_Trackback_SpamCheck_Akismet()

    /**
     * Constructor.
     * Create a new instance of the Akismet spam protection module.
     *
     * @param array $options An array of options for this spam protection module.
     *                       General options are
     *                       'continuous': Whether to continue checking more sources
     *                                     if a match has been found.
     *                       'sources':    List of Akismet servers URIs.
     *
     * @since 0.5.0
     * @access public
     * @return void
     */
    function Services_Trackback_SpamCheck_Akismet($options = null)
    {
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
     * This method is utilized by a Services_Trackback object to check for spam.
     * Generally this method may not be overwritten, but it can be, if necessary.
     * This method calls the _checkSource() method for each source defined in the
     * $_options array (depending on the 'continuous' option), saves the
     * results and returns the spam status determined by the check.
     *
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @access public
     * @return bool Whether the checked object is spam or not.
     */
    function check($trackback)
    {
        $result = $this->_validateOptions($this->_options, $trackback);
        if (PEAR::isError($result)) {
            return $result;
        }

        $foundSpam = false;
        foreach (array_keys($this->_options['sources']) as $id) {
            if ($foundSpam && !$this->_options['continuous']) {
                // We already found spam and shall not continue
                $this->_results[$id] = false;
            } else {
                $res = $this->_checkSource($this->_options['sources'][$id],
                                           $trackback);
                if (PEAR::isError($res)) {
                    return $res;
                }

                $this->_results[$id] = $res;

                $foundSpam = $foundSpam || $res;
            }
        }
        return $foundSpam;
    }

    // }}}
    // {{{ submitSpam()

    /**
     * Submit an invalid, spammy trackback
     *
     * @param Services_Trackback $trackback The trackback
     * @param int                $sourceId  The source ID.
     *
     * @return bool
     */
    function submitSpam($trackback, $sourceId = 0)
    {
        $result = $this->_validateOptions($this->_options, $trackback);
        if (PEAR::isError($result)) {
            return $result;
        }

        $action = 'submit-spam';
        $res    = $this->_sendAkismetRequest($this->_options['sources'][$sourceId],
                                             $trackback, $action);
        if (PEAR::isError($res)) {
            return $res;
        }
        return $res === '';
    }

    // }}}
    // {{{ submitHam()

    /**
     * Submit a valid, non spam trackback
     *
     * @param Services_Trackback $trackback The trackback
     * @param int                $sourceId  The source ID.
     *
     * @return bool
     */
    function submitHam($trackback, $sourceId = 0)
    {
        $result = $this->_validateOptions($this->_options, $trackback);
        if (PEAR::isError($result)) {
            return $result;
        }

        $action = 'submit-ham';
        $res    = $this->_sendAkismetRequest($this->_options['sources'][$sourceId],
                                             $trackback, $action);
        if (PEAR::isError($res)) {
            return $res;
        }
        return $res === '';
    }

    // }}}
    // {{{ verifyKey()

    /**
     * Verify your Akismet key is valid
     *
     * @return bool
     */
    function verifyKey()
    {
        $trackback = Services_Trackback::create(array('id' => 1));

        $res = $this->_sendAkismetRequest($this->_options['sources'][0],
                                          $trackback);
        if (PEAR::isError($res)) {
            return $res;
        }
        return $res == 'valid';
    }

    // }}}

    // {{{ _checkSource()

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the _sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @access protected
     * @return bool True if trackback is spam, false, if not, PEAR_Error on error.
     */
    function _checkSource($source, $trackback)
    {
        $res = $this->_sendAkismetRequest($source, $trackback, 'comment-check');
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res == 'invalid') {
            $error = 'Invalid Akismet request send. Maybe your key is invalid?';
            return PEAR::raiseError($error);
        }
        return ($res == 'true');
    }

    // }}}
    // {{{ _sendAkismetRequest()

    /**
     * Submits a
     *
     * @param string             $baseUri   URI of akisment service
     * @param Services_Trackback $trackback The trackback in question
     * @param string             $action    Action to do
     *
     * @access protected
     * @return string
     */
    function _sendAkismetRequest($baseUri, $trackback, $action = 'verify-key')
    {
        $action = strtolower($action);
        $req    = null;

        $options = $trackback->getOptions();

        $httpRequestOptions = $options['httprequest'];

        switch ($action) {
        case 'verify-key':
            $url = 'http://' . $baseUri . $action;

            $req = new HTTP_Request($url, $httpRequestOptions);
            $req->setMethod(HTTP_REQUEST_METHOD_POST);

            $req->addPostData('key', $this->_options['key']);
            $req->addPostData('blog', $this->_options['url']);
            break;
        case 'comment-check':
        case 'submit-spam':
        case 'submit-ham':
            $url = 'http://' . $this->_options['key'] . '.' . $baseUri . $action;

            $req = new HTTP_Request($url, $httpRequestOptions);
            $req->setMethod(HTTP_REQUEST_METHOD_POST);

            $req->addHeader('User-Agent', $httpRequestOptions['useragent']);
            $req->addPostData('comment_type', 'trackback');

            $req->addPostData('key', $this->_options['key']);
            $req->addPostData('blog', $this->_options['url']);

            $req->addPostData('comment_author', $trackback->get('blog_name'));
            $req->addPostData('comment_author_url', $trackback->get('url'));
            $req->addPostData('user_ip', $trackback->get('host'));

            $extra = $trackback->get('extra');
            $req->addPostData('user_agent', $extra['HTTP_USER_AGENT']);

            $referrer = isset($extra['HTTP_REFERER']) ? $extra['HTTP_REFERER'] : '';
            $req->addPostData('referrer', $referrer);
            break;
        default:
            return PEAR::raiseError('Invalid Akismet action: "'.$action.'".');
            break;
        }

        if (($res = $req->sendRequest()) !== true) {
            return $res;
        }

        if ($req->getResponseCode() !== 200) {
            $error = 'Could not open URL "%s". Code: "%s".';
            return PEAR::raiseError(sprintf($error, $url, $req->getResponseCode()));
        }

        return trim($req->getResponseBody());
    }

    // }}}

    /**
     * Validate an array of options
     *
     * @param mixed[]            $options   Options to validate
     * @param Services_Trackback $trackback Trackback to be used
     *
     * @access private
     * @return bool
     */
    function _validateOptions($options, $trackback)
    {
        $error = 'Missing option "%s". Cannot proceed without it.';

        if (empty($options['url'])) {
            return PEAR::raiseError(sprintf($error, 'url'), 0);
        }
        if (empty($options['key'])) {
            return PEAR::raiseError(sprintf($error, 'key'), 0);
        }
        if (!is_array($options['sources'])
            || count($options['sources']) < 1) {
            return PEAR::raiseError(sprintf($error, 'sources'), 0);
        }

        $extra = $trackback->get('extra');
        if (!is_array($extra) || count($extra) < 1) {
            return PEAR::raiseError(sprintf($error, 'extra'), 0);
        }

        return true;
    }
}
