<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Trackback.
 *
 * This is the main file of the Services_Trackback package. This file has to be
 * included for usage of Services_Trackback.
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
 * @since     File available since Release 0.1.0
 */


require_once 'Services/Trackback/Exception.php';

/**
 * Trackback
 * A generic class to send/receive trackbacks.
 *
 * @category  Webservices
 * @package   Trackback
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright 2005-2006 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Services_Trackback
 * @since     0.1.0
 */
class Services_Trackback
{
    /**
     * This constant is used with the @see Services_Trackback::autodiscover() method.
     * Using this constant you supress the URL check described in the trackback specs.
     */
    const STRICTNESS_LOW = 1;

    /**
     * This constant is used with the @see Services_Trackback::autodiscover() method.
     * Using this constant you use a not so strict URL check than described in the
     * trackback specs. Only the domain name is checked.
     */
    const STRICTNESS_MIDDLE = 2;

    /**
     * This constant is used with the @see Services_Trackback::autodiscover() method.
     * Using this constant activate the URL check described in the trackback specs.
     */
    const STRICTNESS_HIGH = 3;


    /**
     * The necessary trackback data.
     *
     * @var array
     * @since 0.1.0
     */
    protected $data = array(
        'id'            => '',
        'title'         => '',
        'excerpt'       => '',
        'blog_name'     => '',
        'url'           => '',
        'trackback_url' => '',
        'host'          => '',
        'extra'         => array(),
    );

    /**
     * Options to influence Services_Trackback.
     *
     * @see Services_Trackback::create()
     * @since 0.4.0
     * @var array
     */
     protected $options = array(
        // Options for Services_Trackback directly
        'strictness'        => Services_Trackback::STRICTNESS_LOW,
        'timeout'           => 30,          // seconds
        'fetchlines'        => 30,
        'fetchextra'        => true,
        // Options for HTTP_Request class
        'httprequest'       => array(
            'allowRedirects'    => true,
            'maxRedirects'      => 2,
            'useragent'         => 'PEAR Services_Trackback v@package_version@'
        ),
    );

    protected $spamChecks = array();

    /**
     * Factory
     * This static method is used to create a trackback object.
     * (Services_Trackback::create($data))
     * The factory requires a data array as described below for creation. The 'id'
     * key is obligatory for this method. Every other data is not quite necessary
     * for the creation, but might be necessary for calling other methods
     * afterwards. See the specific methods for further info on which data is
     * required.
     *
     * Data:
     *      Required
     *              id            The ID of the trackback target.
     *      Optional
     *              title         string  Title of the trackback.
     *              excerpt       string  Abstract of the trackback.
     *              blog_name     string  Name of the trackback blog.
     *              url           string  URL of the trackback.
     *              trackback_url string  URL to send trackbacks to.
     *              extra         array   Content of $_SERVER, captured
     *                                    while doing
     *                                    Services_Trackback::receive().
     * Options:
     *     strictness     int     The default strictness to use in
     *                            Services_Trackback::autodiscover().
     *     timeout        int     The default timeout for network operations
     *                            in seconds.
     *     fetchlines     int     The max number of lines to fetch over the network.
     *     httprequest    array   The options utilized by HTTP_Request are stored
     *                            here.
     *                            The following options are the most commonly used
     *                            for HTTP_Request in Services_Trackback.
     *                            All other options are supported too, see
     *                            HTTP_Request::HTTP_Request() for more detailed
     *                            documentation.
     *                            Some options for HTTP_Request are overwritten
     *                            through the global settings of
     *                            Services_Trackback (such as timeout).
     *     timeout        float   THE TIMEOUT SETTING IS OVERWRITTEN BY THE GLOBAL
     *                            Services_Trackback SETTING.
     *     allowRedirects bool    Wether to follow HTTP redirects or not.
     *     maxRedirects   int     Maximum number of redirects.
     *     useragent      string  The user agent to use for HTTP requests.
     *
     * @param array $data    Data for the trackback, which is obligatory.
     * @param array $options Options to set for this trackback.
     *
     * @since 0.2.0
     * @static
     * @return Services_Trackback The newly created Trackback.
     */
    public static function create($data, $options = null)
    {
        // Sanity check
        $options = isset($options) && is_array($options) ? $options : array();

        // Create trackback
        $trackback = new Services_Trackback();

        $res = $trackback->fromArray($data);

        $res = $trackback->setOptions($options);

        return $trackback;
    }

    /**
     * setOptions
     * Set options for the trackback.
     *
     * @param array $options Pairs of 'option' => 'value' as described at
     *                       Services_Trackback::create().
     *
     * @since 0.4.0
     * @see Services_Trackback::create()
     * @see Services_Trackback::getOptions()
     * @return mixed Bool true on success, otherwise Services_Trackback_Exception.
     */
    public function setOptions($options)
    {
        foreach ($options as $option => $value) {
            if (!isset($this->options[$option])) {
                $error = 'Desired option "'.$option.'" not available.';
                throw new Services_Trackback_Exception($error);
            }

            $error = 'Invalid value for option "%s", must be %s.';

            switch ($option) {
            case 'strictness':
                if (!is_int($value) || ($value < 1) || ($value > 3)) {
                    $allowed = array('Services_Trackback::STRICTNESS_LOW',
                                     'Services_Trackback::STRICTNESS_MIDDLE',
                                     'Services_Trackback::STRICTNESS_HIGH');
                    throw new Services_Trackback_Exception(sprintf($error, $option,
                                                    implode(', ', $allowed)));
                }
                break;
            case 'timeout':
                if (!is_int($value) || ($value < 0)) {
                    throw new Services_Trackback_Exception(sprintf($error, $option, 'int >= 0'));
                }
                break;
            case 'fetchlines':
                if (!is_int($value) || ($value < 1)) {
                    throw new Services_Trackback_Exception(sprintf($error, $option, 'int >= 1'));
                }
                break;
            case 'fetchextra':
                if (!is_bool($value)) {
                    throw new Services_Trackback_Exception(sprintf($error, $option, 'bool'));
                }
                break;
            case 'httprequest':
                if (!is_array($value)) {
                    throw new Services_Trackback_Exception(sprintf($error, $option, 'array'));
                }
                break;
            }
            $this->options[$option] = $value;
        }
        return true;
    }

    /**
     * getOptions
     * Get the currently set option set.
     *
     * @since 0.4.0
     * @see Services_Trackback::setOptions()
     * @see Services_Trackback::create()
     * @return array The currently active options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * setData
     * Accessor for data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * autodiscover
     * Checks a given URL for trackback autodiscovery code.
     *
     * @since 0.2.0
     * @return bool True on success.
     */
    public function autodiscover()
    {
        $necessaryData = array('url');

        $res = $this->checkData($necessaryData);

        $url = $this->data['url'];

        /*
        Sample autodiscovery code
        <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
          xmlns:dc="http://purl.org/dc/elements/1.1/"
          xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
          <rdf:Description
            rdf:about="http://pear.php.net/package/Net_FTP"
            dc:identifier="http://pear.php.net/package/Net_FTP"
            dc:title="Net_FTP"
            trackback:ping="http://pear.php.net/trackback/trackback.php?id=Net_FTP"/>
        </rdf:RDF>
        */

        // Receive file contents.
        $content = $this->getContent($url);


        $matches = array();
        // Get trackback identifier
        if (!preg_match('@dc:identifier\s*=\s*["\'](http:[^"\']+)"@i',
                        $content, $matches)) {
            throw new Services_Trackback_Exception('No trackback RDF found in "'.$url.'".');
        }
        $identifier = trim($matches[1]);

        // Get trackback URI
        if (!preg_match('@trackback:ping\s*=\s*["\'](http:[^"\']+)"@i',
                        $content, $matches)) {
            throw new Services_Trackback_Exception('No trackback URI found in "'.$url.'".');
        }
        $trackbackUrl = trim($matches[1]);

        $res = $this->checkURLs($url, $identifier, $this->options['strictness']);


        $this->data['trackback_url'] = $trackbackUrl;
        return true;
    }

    /**
     * send
     * This method sends a trackback to the trackback_url saved in it. The
     * data array of the trackback object can be completed by submitting the
     * necessary data through the $data parameter of this method.
     *
     * The following data has to be set to call this method:
     *              'title'             Title of the weblog entry.
     *              'url'               URL of the weblog entry.
     *              'excerpt'           Excerpt of the weblog entry.
     *              'blog_name'         Name of the weblog.
     *              'trackback_url'     URL to send the trackback to.
     *
     * Services_Trackback::send() requires HTTP_Request2. The options
     * for the HTTP_Request2 object are stored in the global options array using
     * the key 'http_request'.
     *
     * @param string $data Additional data to complete the trackback.
     *
     * @since 0.3.0
     * @return mixed True on success.
     */
    public function send($data = null)
    {
        // Consistancy check
        if (!isset($data)) {
            $data = array();
        }

        $this->setData(array_merge($this->getData(), $data));

        $necessaryData = array('title', 'url', 'excerpt',
                               'blog_name', 'trackback_url');

        $res = $this->checkData($necessaryData);


        // Get URL
        $url = str_replace('&amp;', '&', $this->data['trackback_url']);

        // Changed in 0.5.0 All HTTP_Request2 options are now supported.
        $options = $this->options['httprequest'];

        $options['timeout'] = $this->options['timeout'];

        // Create new HTTP_Request2
        $req = new HTTP_Request2($url, $options);
        $req->setMethod(Http_Request2::METHOD_POST);

        // Add HTTP headers
        $req->setHeader("User-Agent", $options['useragent']);

        // Adding data to send
        $req->addPostParameter('url', $this->data['url']);
        $req->addPostParameter('title', $this->data['title']);
        $req->addPostParameter('blog_name', $this->data['blog_name']);
        $req->addPostParameter('excerpt', strip_tags($this->data['excerpt']));

        // Send POST request
        $response = $req->send();

        // Check return code
        if ($response->getStatus() != 200) {
            $error = 'Host returned Error '.$response->getStatus().'.';
            throw new Services_Trackback_Exception($error);
        }

        return $this->interpretTrackbackResponse($response->getBody());
    }

    /**
     * getAutodiscoverCode
     * Returns the RDF Code for a given website to let weblogs autodiscover
     * the possibility of tracking it back.
     * The following data has to be set to call this method:
     *              'id'
     *              'title'
     *              'url'
     *              'trackback_url'
     *
     * @param bool $comments Whether to include HTML comments around the RDF
     *
     * @since 0.1.0
     * @return string RDF code
     */
    public function getAutodiscoveryCode($comments = true)
    {
        $necessaryData = array('title', 'url', 'trackback_url');

        $res = $this->checkData($necessaryData);

        $data = $this->getEncodedData($necessaryData);
        $res  = <<<EOD
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
    <rdf:Description
        rdf:about="%s"
        dc:identifier="%s"
        dc:title="%s"
        trackback:ping="%s" />
</rdf:RDF>
EOD;

        $res = sprintf($res, $data['url'], $data['url'],
                             $data['title'], $data['trackback_url']);
        if ($comments) {
            return "<!--\n".$res."\n-->\n";
        }

        return $res."\n";
    }

    /**
     * receive
     * Receives a trackback. The following data has to be set in
     * the data array to fulfill this operation:
     *      'id'
     *
     * @param mixed[] $data An array of data, ie, from $_POST.
     *
     * @since 0.1.0
     * @return object Services_Trackback
     */
    public function receive($data = null)
    {
        if (!isset($data)) {
            $data = $_POST;

            $data['host'] = $_SERVER['REMOTE_ADDR'];
        }

        $necessaryPostData = array('title', 'excerpt', 'url', 'blog_name', 'host');

        $res = $this->checkData(array('id'));

        $res = $this->checkData($necessaryPostData, $data);

        $decodedData = $this->getDecodedData($necessaryPostData, $data);
        $this->setData(array_merge($this->getData(), $decodedData));
        if ($this->options['fetchextra'] === true) {
            $this->data['extra'] = $_SERVER;
        }
        return true;
    }

    /**
     * getResponseSuccess
     * Returns an XML response for a successful trackback.
     *
     * @since 0.1.0
     * @return string The XML code
     */
    public function getResponseSuccess()
    {
        return <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<response>
<error>0</error>
</response>
EOD;
    }

    /**
     * getResponseError
     * Returns an XML response for a trackback error.
     *
     * @param string $message The error message
     * @param int    $code    The error code
     *
     * @since 0.1.0
     * @return void
     */
    public function getResponseError($message, $code)
    {
        $data = $this->getEncodedData(array('code', 'message'),
                                                    array('code' => $code,
                                                          'message' => $message));

        $res = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<response>
<error>%s</error>
<message>%s</message>
</response>
EOD;
        return sprintf($res, $data['code'], $data['message']);
    }

    /**
     * addSpamCheck
     * Add a spam check module to the trackback.
     *
     * @param Services_Trackback_SpamCheck &$spamCheck The spam check module
     *                                                 to add.
     * @param int                          $priority   A priority value for the spam
     *                                                 check. Lower priority indices
     *                                                 are processed earlier.
     *                                                 If no priority level is set,
     *                                                 0 is assumed.
     *
     * @since 0.5.0
     * @see Services_Trackback::removeSpamCheck()
     * @see Services_Trackback::checkSpam()
     * @return mixed Added SpamCheck module instance on succes
     */
    public function addSpamCheck($spamCheck, $priority = 0)
    {
        $subclass = is_subclass_of($spamCheck, 'Services_Trackback_SpamCheck');
        if (!is_object($spamCheck) || !$subclass) {
            throw new Services_Trackback_Exception('Invalid spam check module.', -1);
        }
        $this->spamChecks[$priority][] = $spamCheck;
        return $spamCheck;
    }

    /**
     * createSpamCheck
     * Create and add a spam check module to the trackback.
     *
     * @param string $spamCheckType Name of the spamcheck module to create and add.
     * @param array  $options       Options for the spamcheckmodule.
     * @param int    $priority      A priority value for the spam check. Lower
     *                              priority indices are processed earlier.
     *                              If no priority level is set, 0 is assumed.
     *
     * @since 0.5.0
     * @see Services_Trackback::addSpamCheck()
     * @see Services_Trackback::removeSpamCheck()
     * @see Services_Trackback::checkSpam()
     * @return mixed Instance of the created SpamCheck module
     */
    public function createSpamCheck($spamCheckType, $options = array(), $priority = 0)
    {
        $filename   = dirname(__FILE__).'/Trackback/SpamCheck.php';
        $createfunc = array('Services_Trackback_SpamCheck', 'create');

        // SpamCheck class already included?
        if (!class_exists($createfunc[0])) {
            if (!file_exists($filename)) {
                $error = 'SpamCheck subclass not found. Broken installation!';
                throw new Services_Trackback_Exception($error);
            } else {
                include_once $filename;
            }
        }

        // SpamCheck class successfully included?
        if (!class_exists($createfunc[0])) {
            $error = 'SpamCheck subclass not found. Broken installation!';
            throw new Services_Trackback_Exception($error);
        }

        $spamCheck = call_user_func($createfunc, $spamCheckType, $options);

        $res = $this->addSpamCheck($spamCheck, $priority);

        return $res;
    }

    /**
     * removeSpamCheck
     * Remove a spam check module from the trackback.
     *
     * @param object(Services_Trackback_SpamCheck) &$spamCheck The spam check module
     *                                                         to remove.
     *
     * @since 0.5.0
     * @see Services_Trackback::addSpamCheck()
     * @see Services_Trackback::checkSpam()
     * @return bool True on success.
     */
    public function removeSpamCheck($spamCheck)
    {
        foreach ($this->spamChecks as $priority => $spamChecks) {
            foreach ($spamChecks as $id => $spamCheck) {
                if ($this->spamChecks[$priority][$id] === $spamCheck) {
                    unset($this->spamChecks[$priority][$id]);
                    return true;
                }
            }
        }
        throw new Services_Trackback_Exception('Given spam check module not found.', -1);
    }

    /**
     * checkSpam
     * Checks the given trackback against several spam protection sources
     * such as DNSBL, SURBL, Word BL,... The sources to check are defined using
     * Services_Trackback_SpamCheck modules.
     *
     * @param bool $continouse Wether to check all spam protection modules or
     *                         quit checking if one modules returns a positive
     *                         result.
     *
     * @since 0.5.0
     * @see Services_Trackback::addSpamCheck()
     * @see Services_Trackback::removeSpamCheck()
     * @return bool True, if one of the sources
     */
    public function checkSpam($continouse = false)
    {
        $spam = false;
        foreach ($this->spamChecks as $priority => $spamChecks) {
            foreach (array_keys($spamChecks) as $id) {
                if (!$continouse && $spam) {
                    // No need to check further
                    $this->spamChecksResults[$priority][$id] = false;
                } else {
                    $tmpRes = $this->spamChecks[$priority][$id]->check($this);

                    $this->spamChecksResults[$priority][$id] = $tmpRes;

                    $spam = ($spam || $tmpRes);
                }
            }
        }
        return $spam;
    }

    /**
     * get
     * Get data from the trackback. Returns the value of a given
     * key.
     *
     * @param string $key The key to fetch a value for.
     *
     * @since 0.2.0
     * @return mixed A string value.
     */
    public function get($key)
    {
        $error = 'Key '.$key.' not found.';
        if (!isset($this->data[$key])) {
            throw new Services_Trackback_Exception($error);
        }
        return $this->data[$key];
    }

    /**
     * set
     * Set data of the trackback. Saves the value of a given
     * key, returning true on success.
     *
     * @param string $key The key to set a value for.
     * @param string $val The value for the key.
     *
     * @since 0.2.0
     * @return mixed Boolean true on success.
     */
    public function set($key, $val)
    {
        $this->data[$key] = $val;
        return true;
    }

    /**
     * Create a Trackback from a $data array.
     *
     * @param array $data The data array (@see Services_Trackback::create()).
     *
     * @since 0.2.0
     * @return mixed True on success.
     */
    public function fromArray($data)
    {
        $res = $this->checkData(array('id'), $data);
        $this->setData($data);

        return true;
    }

    /**
     * getContent
     * Receive the content from a specific URL.
     *
     * @param string $url The URL to download data from.
     *
     * @since 0.4.0
     * @return string The content.
     */
    public function getContent($url)
    {
        $handle = @fopen($url, 'r');
        if (!is_resource($handle)) {
            throw new Services_Trackback_Exception('Could not open URL "'.$url.'"');
        }
        stream_set_timeout($handle, $this->options['timeout']);

        $content = '';
        for ($i = 0; ($i < $this->options['fetchlines']) && !feof($handle);$i++) {
            $content .= fgets($handle);
        }

        return $content;
    }

    /**
     * getEncodedData
     * Receives a number of data from the internal data store, encoded for XML usage.
     *
     * @param array $keys Data keys to receive
     * @param array $data Optionally the data to check (default is the object data).
     *
     * @since 0.1.0
     * @return void
     */
    public function getEncodedData($keys, $data = null)
    {
        if (!isset($data)) {
            $data = $this->getData();
        }

        foreach ($keys as $key) {
            $res[$key] = htmlentities($data[$key]);
        }

        return $res;
    }

    /**
     * getDecodedData
     * Receives a number of data from the internal data store.
     *
     * @param array $keys Data keys to receive
     * @param array $data Optionally the data to check (default is the object data).
     *
     * @since 0.1.0
     * @return void
     */
    public function getDecodedData($keys, $data =  null)
    {
        if (!isset($data)) {
            $data = $this->getData();
        }

        foreach ($keys as $key) {
            $res[$key] = $data[$key];
        }

        return $res;
    }

    /**
     * checkData
     * Checks a given array of keys for the validity of their data.
     *
     * @param array $keys Data keys to check.
     * @param array $data Optionally the data to check (default is the object data).
     *
     * @since 0.1.0
     *
     * @return void
     */
    public function checkData($keys, $data = null)
    {
        if (!isset($data)) {
            $data = $this->getData();
        }

        foreach ($keys as $key) {
            if (empty($data[$key])) {
                throw new Services_Trackback_Exception('Invalid data. Key "'.$key.'" missing.');
            }
        }
        return true;
    }

    /**
     * checkURLs
     * This little method checks if 2 URLs (the URL to trackback against the
     * trackback identifier found in the autodiscovery code) are equal.
     *
     * @param string   $url1       The first URL.
     * @param string   $url2       The second URL.
     * @param constant $strictness How strict to check URLs. Use one of
     *                             Services_Trackback::STRICTNESS_* constants.
     *
     * @see Services_Trackback::autodiscover()
     * @since 0.2.0
     * @return mixed True on success.
     */
    public function checkURLs($url1, $url2, $strictness)
    {
        switch ($strictness) {
        case Services_Trackback::STRICTNESS_HIGH:
            if ($url1 !== $url2) {
                $error = 'URLs mismatch. "'.$url1.'" !== "'.$url2.'".';
                throw new Services_Trackback_Exception($error);
            }
            break;

        case Services_Trackback::STRICTNESS_MIDDLE:
            $matches = array();

            $domainRegex = "@http://([^/]+).*@";

            $res = preg_match($domainRegex, $url1, $matches);
            if (!$res) {
                $error = 'Invalid URL1, no domain part found ("'.$url1.'").';
                throw new Services_Trackback_Exception($error);
            }

            $domain1 = $matches[1];

            $res = preg_match($domainRegex, $url2, $matches);
            if (!$res) {
                $error = 'Invalid URL1, no domain part found ("'.$url1.'").';
                throw new Services_Trackback_Exception($error);
            }

            $domain2 = $matches[1];
            if ($domain1 !== $domain2) {
                $error = 'URLs missmatch. "'.$domain1.'" !== "'.$domain2.'".';
                throw new Services_Trackback_Exception($error);
            }
            break;

        case Services_Trackback::STRICTNESS_LOW:
        default:
            // No checks, when strictness is low.
            break;
        }
        return true;
    }

    /**
     * Interpret the returned XML code, when sending a trackback.
     *
     * @param string $response Raw XML response
     *
     * @see Services_Trackback::send()
     * @since 0.3.0
     * @return void Mixed true on success.
     */
    public function interpretTrackbackResponse($response)
    {
        $matches = array();
        if (!preg_match('@<error>([0-9]+)</error>@', $response, $matches)) {
            $error = 'Invalid trackback response, error code not found.';
            throw new Services_Trackback_Exception($error);
        }
        $errorCode = $matches[1];

        // Error code 0 means no error.
        if ($errorCode == 0) {
            return true;
        }

        if (!preg_match('@<message>([^<]+)</message>@', $response, $matches)) {
            $error = 'Error code '.$errorCode.', no message received.';
            throw new Services_Trackback_Exception($error);
        }


        $error = 'Error code ' . $errorCode
                    . ', message "' . $matches[1] . '" received.';

        throw new Services_Trackback_Exception($error);
    }
}
