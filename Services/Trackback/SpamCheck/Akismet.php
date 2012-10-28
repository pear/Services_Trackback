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
 * @since     File available since Release 0.6.0
 */

require_once 'Services/Trackback/Exception.php';

/**
 * Load SpamCheck base class
 */

require_once 'Services/Trackback/SpamCheck.php';


/**
 * HTTP_Request2 for sending POST requests to Akismet
 */
require_once 'HTTP/Request2.php';

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
 */
class Services_Trackback_SpamCheck_Akismet extends Services_Trackback_SpamCheck
{


    /**
     * Options for the Wordlist.
     *
     * @var array
     * @since 0.5.0
     */
    protected $options = array('continuous'    => false,
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
        $result = $this->validateOptions($this->options, $trackback);

        $foundSpam = false;
        foreach (array_keys($this->options['sources']) as $id) {
            if ($foundSpam && !$this->options['continuous']) {
                // We already found spam and shall not continue
                $this->results[$id] = false;
            } else {
                $res = $this->checkSource($this->options['sources'][$id],
                                           $trackback);

                $this->results[$id] = $res;

                $foundSpam = $foundSpam || $res;
            }
        }
        return $foundSpam;
    }

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
        $result = $this->validateOptions($this->options, $trackback);

        $action = 'submit-spam';
        $res    = $this->sendAkismetRequest($this->options['sources'][$sourceId],
                                             $trackback, $action);
        return $res === '';
    }

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
        $result = $this->validateOptions($this->options, $trackback);

        $action = 'submit-ham';
        $res    = $this->sendAkismetRequest($this->options['sources'][$sourceId],
                                             $trackback, $action);
        return $res === '';
    }

    /**
     * Verify your Akismet key is valid
     *
     * @return bool
     */
    function verifyKey()
    {
        $trackback = Services_Trackback::create(array('id' => 1));

        $res = $this->sendAkismetRequest($this->options['sources'][0],
                                          $trackback);
        return $res == 'valid';
    }

    /**
     * Check a specific source if a trackback has to be considered spam.
     *
     * @param mixed              $source    Element of the _sources array to check.
     * @param Services_Trackback $trackback The trackback to check.
     *
     * @since 0.5.0
     * @return bool True if trackback is spam, false, if not, Services_Trackback_Exception.
     */
    function checkSource($source, $trackback)
    {
        $res = $this->sendAkismetRequest($source, $trackback, 'comment-check');
        if ($res == 'invalid') {
            throw new Services_Trackback_Exception(
                'Invalid Akismet request send. Maybe your key is invalid?'
            );
        }
        return ($res == 'true');
    }

    /**
     * Submits a
     *
     * @param string             $baseUri   URI of akisment service
     * @param Services_Trackback $trackback The trackback in question
     * @param string             $action    Action to do
     *
     * @return string
     */
    function sendAkismetRequest($baseUri, $trackback, $action = 'verify-key')
    {
        $action = strtolower($action);
        $req    = null;

        $options = $trackback->getOptions();

        $httpRequestOptions = $options['httprequest'];

        switch ($action) {
        case 'verify-key':
            $url = 'http://' . $baseUri . $action;

            $req = new HTTP_Request2($url, $httpRequestOptions);
            $req->setMethod(HTTP_Request2::METHOD_POST);

            $req->addPostParameter('key', $this->options['key']);
            $req->addPostParameter('blog', $this->options['url']);
            break;
        case 'comment-check':
        case 'submit-spam':
        case 'submit-ham':
            $url = 'http://' . $this->options['key'] . '.' . $baseUri . $action;

            $req = new HTTP_Request2($url, $httpRequestOptions);
            $req->setMethod(HTTP_Request2::METHOD_POST);

            $req->setHeader('User-Agent', $httpRequestOptions['useragent']);
            $req->addPostParameter('comment_type', 'trackback');

            $req->addPostParameter('key', $this->options['key']);
            $req->addPostParameter('blog', $this->options['url']);

            $req->addPostParameter('comment_author', $trackback->get('blog_name'));
            $req->addPostParameter('comment_author_url', $trackback->get('url'));
            $req->addPostParameter('user_ip', $trackback->get('host'));

            $extra = $trackback->get('extra');
            $req->addPostParameter('user_agent', $extra['HTTP_USER_AGENT']);

            $referrer = isset($extra['HTTP_REFERER']) ? $extra['HTTP_REFERER'] : '';
            $req->addPostParameter('referrer', $referrer);
            break;
        default:
            throw new Services_Trackback_Exception(
                'Invalid Akismet action: "'.$action.'".'
            );
            break;
        }

        $response = $req->send();

        if ($response->getStatus() !== 200) {
            $error = 'Could not open URL "%s". Code: "%s".';
            throw new Services_Trackback_Exception(sprintf($error, $url, $response->getStatus()));
        }

        return trim($response->getBody());
    }

    /**
     * Validate an array of options
     *
     * @param mixed[]            $options   Options to validate
     * @param Services_Trackback $trackback Trackback to be used
     *
     * @return bool
     */
    function validateOptions($options, $trackback)
    {
        $error = 'Missing option "%s". Cannot proceed without it.';

        if (empty($options['url'])) {
            throw new Services_Trackback_Exception(sprintf($error, 'url'), 0);
        }
        if (empty($options['key'])) {
            throw new Services_Trackback_Exception(sprintf($error, 'key'), 0);
        }
        if (!is_array($options['sources'])
            || count($options['sources']) < 1) {
            throw new Services_Trackback_Exception(sprintf($error, 'sources'), 0);
        }

        $extra = $trackback->get('extra');
        if (!is_array($extra) || count($extra) < 1) {
            throw new Services_Trackback_Exception(sprintf($error, 'extra'), 0);
        }

        return true;
    }
}
