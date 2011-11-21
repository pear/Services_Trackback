#!/bin/bash
php Services_Trackback_TestAll.php|grep 'failed.'
echo "Running test suite finished.";
