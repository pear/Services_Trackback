<?php

// No spam
$trackbackData['nospam'] = array(
    'id'        => 1,
    'host'        => '217.160.181.63',
    'title'     => 'Proposals as wide as you can see...',
    'excerpt'   => 'After a pretty bunge of work last night (written RFCs, created surveys, fixed developement environment,...) this morning I posted an RFC and a proposol on<a href="http://marc.theaimsgroup.com/?l=pear-dev"> pear-dev</a>',
    'url'       => 'http://www.schlitt.info/applications/blog/archives/33_Proposals_as_wide_as_you_can_see.html',
    'blog_name' => 'Proposals as wide as you can see...',
    'trackback_url' => 'http://www.example.com/',
);

// Spam - undetected
$trackbackData['undetected'] = array(
    'id'        => 1,
    'host'        => '217.160.181.63',
    'title'     => 'Test',
    'excerpt'   => 'Test',
    'url'       => 'http://www.schlitt.info/',
    'blog_name' => 'Tobias Schlitt, Weblog',
    'trackback_url' => 'http://www.schlitt.info/',
);

// Spam - detectable by host (DNSBL), title (Wordlist), excerpt (Wordlist, SURBL), url (SURBL), blog_name (Wordlist)
$trackbackData['all'] = array(
    'id'        => 1,
    'host'      => '127.0.0.2',
    'title'     => 'xanax',
    'excerpt'   => 'You can also check the porn sites about <A HREF="http://www.e-poker-777.com/free-online-poker.html">free online diet</A>',
    'url'       => 'http://www.e-poker-777.com/funny-crap-to-download.html',
    'blog_name' => 'viagra-test-123',
    'trackback_url' => 'http://www.example.com/',
);


// Spam - detectable by host (DNSBL)
$trackbackData['host'] = array(
    'id'        => 1,
    'host'      => '127.0.0.2',
    'title'     => 'texas holdem',
    'excerpt'   => 'You may find it interesting to visit the sites about <A HREF="http://www.ua-princeton.com/">texas holdem</A>',
    'url'       => 'http://www.ua-princeton.com/',
    'blog_name' => 'texas holdem',
    'trackback_url' => 'http://www.example.com/',
);

// Spam - detectable by title (Wordlist)
$trackbackData['title'] = array(
    'id'        => 1,
    'host'      => '210.240.77.8',
    'title'     => 'Sex porn anal oral',
    'excerpt'   => 'You may find it interesting to visit the sites about <A HREF="http://www.ua-princeton.com/">texas holdem</A>',
    'url'       => 'http://www.ua-princeton.com/',
    'blog_name' => 'texas holdem',
    'trackback_url' => 'http://www.example.com/',
);

// Spam - detectable by excerpt (Wordlist, SURBL)
$trackbackData['excerpt'] = array(
    'id'        => 1,
    'host'        => '210.240.77.8',
    'title'     => 'texas holdem',
    'excerpt'   => 'You can also check the sites about <A HREF="http://www.e-poker-777.com/free-online-poker.html">free online poker</A> <A HREF="http://www.juris-net.com/video-poker.html">video poker</A>',
    'url'       => 'http://www.ua-princeton.com/',
    'blog_name' => 'texas holdem',
    'trackback_url' => 'http://www.example.com/',
);

// Spam - detectable url (SURBL)
$trackbackData['url'] = array(
    'id'        => 1,
    'host'        => '210.240.77.8',
    'title'     => 'texas holdem',
    'excerpt'   => 'You may find it interesting to visit the sites about <A HREF="http://www.ua-princeton.com/">texas holdem</A>',
    'url'       => 'http://www.e-poker-777.com/funny-crap-to-download.html',
    'blog_name' => 'texas holdem',
    'trackback_url' => 'http://www.example.com/',
);

// Spam - detectable blog_name (Wordlist)
$trackbackData['blog_name'] = array(
    'id'        => 1,
    'host'        => '210.240.77.8',
    'title'     => 'texas holdem',
    'excerpt'   => 'You may find it interesting to visit the sites about <A HREF="http://www.ua-princeton.com/">texas holdem</A>',
    'url'       => 'http://www.ua-princeton.com/',
    'blog_name' => 'Porn sex poker',
    'trackback_url' => 'http://www.example.com/',
);

?>
