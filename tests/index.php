<?php

/**
 * Pinga Session
 *
 * Written in 2023 by Taras Kondratyuk (https://getpinga.com)
 * Based on PHP-Cookie (https://github.com/delight-im/PHP-Cookie) by delight.im (https://www.delight.im/)
 *
 * @license MIT
 */

// enable error reporting
\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

\header('Content-type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';

/* BEGIN TEST SESSION */

(isset($_SESSION) === false) or \fail(__LINE__);
(\Pinga\Session\Session::id() === '') or \fail(__LINE__);

\Pinga\Session\Session::start();
$sessionCookieReferenceHeader = \Pinga\Http\ResponseHeader::take('Set-Cookie');
session_write_close();

\Pinga\Session\Session::start(null);
\testEqual(\Pinga\Http\ResponseHeader::take('Set-Cookie'), \str_replace('; SameSite=Lax', '', $sessionCookieReferenceHeader));
session_write_close();

@\Pinga\Session\Session::start('None');
\testEqual(\Pinga\Http\ResponseHeader::take('Set-Cookie'), \str_replace('; SameSite=Lax', '; SameSite=None', $sessionCookieReferenceHeader));
session_write_close();

\Pinga\Session\Session::start('Lax');
\testEqual(\Pinga\Http\ResponseHeader::take('Set-Cookie'), $sessionCookieReferenceHeader);
session_write_close();

\Pinga\Session\Session::start('Strict');
\testEqual(\Pinga\Http\ResponseHeader::take('Set-Cookie'), \str_replace('; SameSite=Lax', '; SameSite=Strict', $sessionCookieReferenceHeader));
session_write_close();

\Pinga\Session\Session::start();

(isset($_SESSION) === true) or \fail(__LINE__);
(\Pinga\Session\Session::id() !== '') or \fail(__LINE__);

$oldSessionId = \Pinga\Session\Session::id();
\Pinga\Session\Session::regenerate();
(\Pinga\Session\Session::id() !== $oldSessionId) or \fail(__LINE__);
(\Pinga\Session\Session::id() !== null) or \fail(__LINE__);

\session_unset();

(isset($_SESSION['key1']) === false) or \fail(__LINE__);
(\Pinga\Session\Session::has('key1') === false) or \fail(__LINE__);
(\Pinga\Session\Session::get('key1') === null) or \fail(__LINE__);
(\Pinga\Session\Session::get('key1', 5) === 5) or \fail(__LINE__);
(\Pinga\Session\Session::get('key1', 'monkey') === 'monkey') or \fail(__LINE__);

\Pinga\Session\Session::set('key1', 'value1');

(isset($_SESSION['key1']) === true) or \fail(__LINE__);
(\Pinga\Session\Session::has('key1') === true) or \fail(__LINE__);
(\Pinga\Session\Session::get('key1') === 'value1') or \fail(__LINE__);
(\Pinga\Session\Session::get('key1', 5) === 'value1') or \fail(__LINE__);
(\Pinga\Session\Session::get('key1', 'monkey') === 'value1') or \fail(__LINE__);

(\Pinga\Session\Session::take('key1') === 'value1') or \fail(__LINE__);
(\Pinga\Session\Session::take('key1') === null) or \fail(__LINE__);
(\Pinga\Session\Session::take('key1', 'value2') === 'value2') or \fail(__LINE__);
(isset($_SESSION['key1']) === false) or \fail(__LINE__);
(\Pinga\Session\Session::has('key1') === false) or \fail(__LINE__);

\Pinga\Session\Session::set('key2', 'value3');

(isset($_SESSION['key2']) === true) or \fail(__LINE__);
(\Pinga\Session\Session::has('key2') === true) or \fail(__LINE__);
(\Pinga\Session\Session::get('key2', 'value4') === 'value3') or \fail(__LINE__);
\Pinga\Session\Session::delete('key2');
(\Pinga\Session\Session::get('key2', 'value4') === 'value4') or \fail(__LINE__);
(\Pinga\Session\Session::get('key2') === null) or \fail(__LINE__);
(\Pinga\Session\Session::has('key2') === false) or \fail(__LINE__);

\session_destroy();
\Pinga\Http\ResponseHeader::take('Set-Cookie');

/* END TEST SESSION */

echo 'ALL TESTS PASSED' . "\n";

function testEqual($actualValue, $expectedValue) {
	$actualValue = (string) $actualValue;
	$expectedValue = (string) $expectedValue;

	echo '[';
	echo $expectedValue;
	echo ']';
	echo "\n";

	if (\strcasecmp($actualValue, $expectedValue) !== 0) {
		echo 'FAILED: ';
		echo '[';
		echo $actualValue;
		echo ']';
		echo ' !== ';
		echo '[';
		echo $expectedValue;
		echo ']';
		echo "\n";

		exit;
	}
}

function fail($lineNumber) {
	exit('Error in line ' . $lineNumber);
}
