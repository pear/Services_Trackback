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
 * @category   Webservices
 * @package    Trackback
 * @author     Tobias Schlitt <toby@php.net>
 * @copyright  2005-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Services_Trackback
 * @since      File available since Release 0.6.0
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
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @category   Webservices
 * @package    Trackback
 * @author     Tobias Schlitt <toby@php.net>
 * @copyright  2005-2006 The PHP Group
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Services_Trackback
 * @since      0.6.0
 * @access     public
 */
class Services_Trackback_SpamCheck_Akismet extends Services_Trackback_SpamCheck {

    // {{{ _options
    
    /**
     * Options for the Wordlist.
     *
     * @var array
     * @since 0.5.0
     * @access protected
     */
    var $_options = array(
        'continuous'    => false,
        'sources'       => array(
            'rest.akismet.com/1.1/',
        ),
        // URL of the blog sending the Akismet request
        'url'           => '',
        // WordPress.com API key to use
        'key'           => '',
        'elements'      => array(
            'title',
            'excerpt',
            'blog_name',
            'url',
            'host',
        ),
    );

    // }}}    
    // {{{ Services_Trackback_SpamCheck_Akismet()

    /**
     * Constructor.
     * Create a new instance of the Akismet spam protection module.
     *
     * @since 0.5.0
     * @access public
     * @param array $options An array of options for this spam protection module. General options are
     *                       'continuous':  Whether to continue checking more sources, if a match has been found.
     *                       'sources':     List of Akismet servers URIs.
     * @return object(Services_Trackback_SpamCheck_Akismet) The newly created SpamCheck object.
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

    function check($trackback)
    {
        if (empty($this->_options['url'])) {
            return PEAR::raiseError('Missing option "url". Cannot procede without it.', 0);
        }
        if (empty($this->_options['key'])) {
            return PEAR::raiseError('Missing option "key". Cannot procede without it.', 0);
        }
        if (!is_array($this->_options['sources']) || count($this->_options['sources']) < 1) {
            return PEAR::raiseError('Missing option "sources". Cannot procede without it.', 0);
        }
        if (!is_array(($extra = $trackback->get('extra'))) || count($extra) < 1){
            return PEAR::raiseError('Missing data "extra". Cannot procede without it.', 0);
        }
        $foundSpam = false;
        foreach ($this->_options['sources'] as $id => $source) {
            if ($foundSpam && !$this->_options['continuous']) {
                // We already found spam and shall not continue
                $this->_results[$id] = false;
            } else {
                if (PEAR::isError($res = $this->_checkSource($this->_options['sources'][$id], $trackback))) {
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

    function submitSpam($trackback, $sourceId = 0)
    {
        if (empty($this->_options['url'])) {
            return PEAR::raiseError('Missing option "url". Cannot procede without it.', 0);
        }
        if (empty($this->_options['key'])) {
            return PEAR::raiseError('Missing option "key". Cannot procede without it.', 0);
        }
        if (!is_array($this->_options['sources']) || count($this->_options['sources']) < 1) {
            return PEAR::raiseError('Missing option "sources". Cannot procede without it.', 0);
        }
        if (!is_array(($extra = $trackback->get('extra'))) || count($extra) < 1){
            return PEAR::raiseError('Missing data "extra". Cannot procede without it.', 0);
        }
        if (PEAR::isError($res = $this->_sendAkismetRequest($this->_options['sources'][$sourceId], $trackback, $action = 'submit-spam'))) {
            return $res;
        }
        return $res === '';
    }

    // }}}  
    // {{{ submitHam()

    function submitHam($trackback, $sourceId = 0)
    {
        if (empty($this->_options['url'])) {
            return PEAR::raiseError('Missing option "url". Cannot procede without it.', 0);
        }
        if (empty($this->_options['key'])) {
            return PEAR::raiseError('Missing option "key". Cannot procede without it.', 0);
        }
        if (!is_array($this->_options['sources']) || count($this->_options['sources']) < 1) {
            return PEAR::raiseError('Missing option "sources". Cannot procede without it.', 0);
        }
        if (!is_array(($extra = $trackback->get('extra'))) || count($extra) < 1){
            return PEAR::raiseError('Missing data "extra". Cannot procede without it.', 0);
        }
        if (PEAR::isError($res = $this->_sendAkismetRequest($this->_options['sources'][$sourceId], $trackback, $action = 'submit-ham'))) {
            return $res;
        }
        return $res === '';
    }

    // }}}  
    // {{{ verifyKey()
    
    function verifyKey()
    {
        $tmpTrack = Services_Trackback::create(array('id' => 1));
        if (PEAR::isError($res = $this->_sendAkismetRequest($this->_options['sources'][0], $tmpTrack))) {
            var_dump($res);
            return $res;
        }
        return $res == 'valid';
    }
    
    // }}}

    // {{{ _checkSource()
    
    function _checkSource(&$source, $trackback)
    {
        if (PEAR::isError($res = $this->_sendAkismetRequest($source, $trackback, 'comment-check'))) {
            return $res;
        }
        if ($res == 'invalid') {
            return PEAR::raiseError('Invalid Akismet request send. Maybe your key is invalid?');
        }
        return ($res == 'true');
    }

    // }}}
    // {{{ _sendAkismetRequest()

    function _sendAkismetRequest($baseUri, $trackback, $action = 'verify-key')
    {
        $action = strtolower($action);
        $req = null;
        
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
                $req->addPostData('referrer', isset($extra['HTTP_REFERER']) ? $extra['HTTP_REFERER'] : '');
            break;
            default:
                return PEAR::raiseError('Invalid Akismet action: "'.$action.'".');
                break;
        }
        
        if (($res = $req->sendRequest()) !== true) {
            return $res;
        }

        if ($req->getResponseCode() !== 200) {
            return PEAR::raiseError('Could not open URL "'.$url.'". Code: "'.$req->getResponseCode().'".');
        }
        
        return trim($req->getResponseBody());
    }

    // }}}
    
}
