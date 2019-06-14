<?php
/*
Global include for all Test files.  Will become a class as well eventually

*/


// $test_config = require_once('/Test.cfg');

function assert_failure($script, $line, $message) {
	echo "script: ".$script . " line: ". $line . " message: ". $message;
	throw new \Exception();
}

error_reporting(E_ALL);
assert_options(ASSERT_ACTIVE,   true);
assert_options(ASSERT_BAIL,     true);
assert_options(ASSERT_WARNING,  false);
assert_options(ASSERT_CALLBACK, 'assert_failure');

