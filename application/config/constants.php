<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| HTTP_VERB
|--------------------------------------------------------------------------
*/
defined('HTTP_VERB_GET')    OR define('HTTP_VERB_GET',    'GET');
defined('HTTP_VERB_POST')   OR define('HTTP_VERB_POST',   'POST');
defined('HTTP_VERB_PUT')    OR define('HTTP_VERB_PUT',    'PUT');
defined('HTTP_VERB_DELETE') OR define('HTTP_VERB_DELETE', 'DELETE');
defined('HTTP_VERB_PATCH')  OR define('HTTP_VERB_PATCH',  'PATCH');

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/
defined('RESULT_OK')   OR define('RESULT_OK',   1);
defined('RESULT_FAIL') OR define('RESULT_FAIL', 0);
define('constant', [
    'url' => 'https://api.digitalocean.com',
    'domainName' => 'ezest-test.com', // Domain Name
    'token' => 'Bearer c29097895d99361a1d17ac09c0da06d25016d3bd227f3f599cc695b9e2a08392',
    'image' => '64936907',
    'ssh_keys' => 'b8:c4:77:81:38:de:5b:ef:d3:cf:0b:0a:88:51:6d:6b',
    // 'domainName' => 'vidyabharatilms.com', // 
    // 'token' => 'Bearer 4051dd1281ee7fe2d4d4fb13c4a60b43e720628eb4ff91d81324df211093b1de', // by vijay
    // 'image' => '66342215', //vijay
    // 'ssh_keys' => 'ee:28:90:b5:66:4d:68:69:34:5f:7a:03:e6:ff:55:6e', //vijay
    'server_participants_limit' => 100,
    'buffer_participant_count' => 5,
    'region' => 'BLR1',
    'size' => 's-4vcpu-8gb',
    'db_host' => 'localhost', // Database host (e.g. localhost)
    'db_name' => 'vidyabharti', // Database name
    'db_username' => 'root', // Database username
    'db_password' => '', // Database password
    'free_memory_threshold' => 80, // free_memory_threshold
    'available_memory_threshold' => 5.0, // available_memory_threshold
    'cpuLoad_threshold' => 0.01, // cpuLoad_threshold
]);