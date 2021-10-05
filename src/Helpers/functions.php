<?php
/**
 * Helper functions
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

 // This is not a good solution, but the keyword global is added here
 // in order to have workable tests
 global $config;
 $config = include(__DIR__ . '/../../config/config.php');

/**
 * Checks if the value of the param is empty and sets it to null
 *
 * @param mixed $param
 *
 * @return mixed
 */
function makeNullIfEmpty($param)
{
    if (empty($param)) {
        return null;
    }

    return $param;
}

/**
 * Triggers headers for a 503
 */
function trigger503Response()
{
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 60');
    die;
}

/**
 * Die and dump passed variable
 *
 * @param mixed $d
 */
function dd($d)
{
    echo '<pre>';
    var_dump($d);
    echo '</pre>';
    die;
}

/**
 * Format and dump passed variable
 *
 * @param mixed $d
 *
 * @return string
 */
function d($d)
{
    echo '<pre>';
    var_dump($d);
    echo '</pre>';
}

/**
 * Find the value of named config
 *
 * @param \PDO $database
 * @param string $name
 *
 * @return string|false
 */
function getConfig(\PDO $database, $name)
{
    try {
        $stmt = $database->prepare("SELECT * FROM configs WHERE name = ?");
        $stmt->execute([$name]);
        $resp = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $resp['value'];
    } catch (\Exception $e) {
        error_log($e->getMessage());
    }

    return false;
}

/**
 * Set the value of a configuration item
 *
 * @param \PDO $database
 * @param string $name
 * @param string $value
 *
 * @return bool
 */
function setConfig(\PDO $database, $name, $value)
{
    try {
        $stmt = $database->prepare("INSERT INTO configs (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");

        return $stmt->execute([$name, $value, $value]);
    } catch (\Exception $e) {
        error_log($e->getMessage());
    }

    return false;
}

/**
 * Database Connection
 *
 * @return \PDO
 * @throws \Exception
 */
function getDbConnection() {
    global $config;

    try {
        $dsn = 'mysql:host=' . $config['db']['params']['host'] . ';dbname=' . $config['db']['params']['dbname'] . ';charset=utf8';
        $pdo = new \PDO($dsn, $config['db']['params']['username'], $config['db']['params']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (\Exception $e) {
        error_log($e->getMessage());
        trigger503Response();
    }
}

/**
 * Generates a secure id by mixing unix timestamp and function uniqid.
 * The uniqueness of the generated id is guaranteed by UNIX timestamp.
 *
 * @return string
 */
function generateSecureId()
{
    return str_replace('.', '', str_replace(' ', '', microtime())) . uniqid();
}

/**
 * Checks if the format of the given date is correct
 *
 * @param string $date
 * @param string $format
 * @param bool $strict
 *
 * @return bool
 */
function verifyDate(string $date, string $format = 'm/d/Y', bool $strict = true)
{
    $dateTime = \DateTime::createFromFormat($format, $date);
    if ($strict) {
        $errors = \DateTime::getLastErrors();
        if (!empty($errors['warning_count'])) {
            return false;
        }
    }

    return $dateTime !== false;
}

/**
 * Helper method to see if Email is Valid
 *
 * @param string $email
 *
 * @return boolean
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Format an exception interface to our log format
 *
 * @param \Exception $e
 *
 * @return string
 */
function formatException(\Exception $e)
{
    return $e->getMessage() . ' - ' . $e->getTraceAsString();
}

/**
 * Checks the request's params for Initiator
 *
 * @param \Slim\Http\Request $request
 *
 * @return array
 */
function checkRequestForInitiator(\Slim\Http\Request $request)
{
    $result['validationMessage'] = [];

    $params = [
        'secureId' => 100,
        'name' => 1000,
        'address' => 255,
        'email' => 255,
        'phone' => 255,
        'website' => 255,
        'additionalInfo' => 400,
        'identifier' => 65535,
        'type' => 255,
        'rank' => 255,
        'status' => 255,
    ];

    foreach ($params as $param => $limit) {
        $paramValue = filter_var($request->getParam($param), FILTER_SANITIZE_STRING);

        if (!empty($paramValue) && strlen($paramValue) > $params[$param]) {
            $result['validationMessage'][] = $param . ' should have maximum length of ' . $limit . ' characters';
        }

        $result[$param] = $paramValue;

        if (empty($paramValue)) {
            $result[$param] = null;
        }
    }

    $dateParams = [
    ];

    foreach ($dateParams as $dateParam => $format) {
        $paramValue = $request->getParam($dateParam);

        if (!empty($paramValue) && !verifyDate($paramValue, $format)) {
            $result['validationMessage'][] = $dateParam . ' should have date in the format ' . $format;
        }

        $result[$dateParam] = $paramValue;

        if (empty($paramValue)) {
            $result[$dateParam] = null;
        }
    }

    return $result;
}

/**
 * Checks the request's params for Appearance
 *
 * @param \Slim\Http\Request $request
 *
 * @return array
 */
function checkRequestForAppearance(\Slim\Http\Request $request)
{
    $result['validationMessage'] = [];

    $params = [
        'secureId' => 100,
        'mode' => 255,
        'reason' => 400,
        'appearanceStartDatetime' => 255,
        'appearanceEndDatetime' => 255,
        'type' => 255,
        'rank' => 255,
        'status' => 255,
    ];

    foreach ($params as $param => $limit) {
        $paramValue = filter_var($request->getParam($param), FILTER_SANITIZE_STRING);

        if (!empty($paramValue) && strlen($paramValue) > $params[$param]) {
            $result['validationMessage'][] = $param . ' should have maximum length of ' . $limit . ' characters';
        }

        $result[$param] = $paramValue;

        if (empty($paramValue)) {
            $result[$param] = null;
        }
    }

    $dateParams = [
        'appearanceStartDatetime' => 'Y/m/d H:i:s',
        'appearanceEndDatetime' => 'Y/m/d H:i:s',
    ];

    foreach ($dateParams as $dateParam => $format) {
        $paramValue = $request->getParam($dateParam);

        if (!empty($paramValue) && !verifyDate($paramValue, $format)) {
            $result['validationMessage'][] = $dateParam . ' should have date in the format ' . $format;
        }

        $result[$dateParam] = $paramValue;

        if (empty($paramValue)) {
            $result[$dateParam] = null;
        }
    }

    return $result;
}

/**
 * Checks the request's params for Guide
 *
 * @param \Slim\Http\Request $request
 *
 * @return array
 */
function checkRequestForGuide(\Slim\Http\Request $request)
{
    $result['validationMessage'] = [];

    $params = [
        'secureId' => 100,
        'firstName' => 255,
        'middleName' => 255,
        'lastName' => 255,
        'birthDate' => 255,
        'email' => 255,
        'address' => 255,
        'affiliation' => 255,
        'jobTitle' => 255,
        'country' => 255,
        'education' => 255,
        'phone' => 255,
        'imagePath' => 255,
        'additionalInfo' => 400,
        'position' => 255,
        'description' => 400,
        'type' => 255,
        'rank' => 255,
        'status' => 255,
    ];

    foreach ($params as $param => $limit) {
        $paramValue = filter_var($request->getParam($param), FILTER_SANITIZE_STRING);

        if (!empty($paramValue) && strlen($paramValue) > $params[$param]) {
            $result['validationMessage'][] = $param . ' should have maximum length of ' . $limit . ' characters';
        }

        $result[$param] = $paramValue;

        if (empty($paramValue)) {
            $result[$param] = null;
        }
    }

    $dateParams = [
        'birthDate' => 'Y/m/d',
    ];

    foreach ($dateParams as $dateParam => $format) {
        $paramValue = $request->getParam($dateParam);

        if (!empty($paramValue) && !verifyDate($paramValue, $format)) {
            $result['validationMessage'][] = $dateParam . ' should have date in the format ' . $format;
        }

        $result[$dateParam] = $paramValue;

        if (empty($paramValue)) {
            $result[$dateParam] = null;
        }
    }

    return $result;
}

/**
 * Checks the request's params for Language
 *
 * @param \Slim\Http\Request $request
 *
 * @return array
 */
function checkRequestForLanguage(\Slim\Http\Request $request)
{
    $result['validationMessage'] = [];

    $params = [
        'secureId' => 100,
        'name' => 255,
        'additional' => 400,
        'description' => 400,
        'type' => 255,
        'rank' => 255,
        'status' => 255,
    ];

    foreach ($params as $param => $limit) {
        $paramValue = filter_var($request->getParam($param), FILTER_SANITIZE_STRING);

        if (!empty($paramValue) && strlen($paramValue) > $params[$param]) {
            $result['validationMessage'][] = $param . ' should have maximum length of ' . $limit . ' characters';
        }

        $result[$param] = $paramValue;

        if (empty($paramValue)) {
            $result[$param] = null;
        }
    }

    $dateParams = [
    ];

    foreach ($dateParams as $dateParam => $format) {
        $paramValue = $request->getParam($dateParam);

        if (!empty($paramValue) && !verifyDate($paramValue, $format)) {
            $result['validationMessage'][] = $dateParam . ' should have date in the format ' . $format;
        }

        $result[$dateParam] = $paramValue;

        if (empty($paramValue)) {
            $result[$dateParam] = null;
        }
    }

    return $result;
}

/**
 * Checks the request's params for Excursion
 *
 * @param \Slim\Http\Request $request
 *
 * @return array
 */
function checkRequestForExcursion(\Slim\Http\Request $request)
{
    $result['validationMessage'] = [];

    $params = [
        'secureId' => 100,
        'groupMembersCount' => 1000,
        'expectedExcursionStartDate' => 255,
        'expectedExcursionStartTime' => 255,
        'verifyStartTimeInHours' => 24,
        'expectedDurationOfExcursion' => 65535,
        'excursionStartDate' => 255,
        'excursionStartTime' => 255,
        'excursionEndTime' => 255,
        'country' => 255,
        'description' => 400,
        'expectedGroupMembersCount' => 1000,
        'radioGuide' => 3,
        'isFree' => 3,
        'additionalInfo' => 500,
        'type' => 255,
        'rank' => 255,
        'status' => 255,
    ];

    foreach ($params as $param => $limit) {
        $paramValue = filter_var($request->getParam($param), FILTER_SANITIZE_STRING);

        if (!empty($paramValue) && strlen($paramValue) > $params[$param]) {
            $result['validationMessage'][] = $param . ' should have maximum length of ' . $limit . ' characters';
        }

        $result[$param] = $paramValue;

        if (empty($paramValue)) {
            $result[$param] = null;
        }
    }

    $dateParams = [
        'expectedExcursionStartDate' => 'Y/m/d',
        'expectedExcursionStartTime' => 'H:i:s',
        'excursionStartDate' => 'Y/m/d',
        'excursionStartTime' => 'H:i:s',
        'excursionEndTime' => 'H:i:s',
    ];

    $fieldDeletingValues = [
        'delete_excursion_start_date',
        'delete_excursion_start_time',
        'delete_excursion_end_time',
        'delete_expected_excursion_start_time'
    ];

    foreach ($dateParams as $dateParam => $format) {
        $paramValue = $request->getParam($dateParam);

        if (!in_array($paramValue, $fieldDeletingValues)) {
            if (!empty($paramValue) && !verifyDate($paramValue, $format)) {
                $result['validationMessage'][] = $dateParam . ' should have date in the format ' . $format;
            }
        }

        $result[$dateParam] = $paramValue;

        if (empty($paramValue)) {
            $result[$dateParam] = null;
        }
    }

    return $result;
}

/**
 * Generates a json web token (jwt)
 *
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 * @param string $alg
 *
 * @param DateTime $jwtValidTime
 * @param DateTime $jwtExpirationTime
 * @param array $data
 *
 * @return string
 */
function encodeJWT($data = [], \DateTime $jwtValidTime = null, \DateTime $jwtExpirationTime = null, string $alg = "HS256") {
    // JWT secret
    $key = getenv("JWT_SECRET");

    // Issued at - Identifies the time at which the JWT was issued. The value must be a NumericDate.
    $jwtIssueTime = new \DateTime();

    // Not Before - Identifies the time on which the JWT will start to be accepted for processing. The value must be a NumericDate.
    if (is_null($jwtValidTime)) {
        $jwtValidTime = new \DateTime("now +0 second");
    }

    // Expiration Time - Identifies the expiration time on and after which the JWT must not be accepted for processing. The value must be a NumericDate:[9] either an integer or decimal, representing seconds past 1970-01-01 00:00:00Z.
    if (is_null($jwtExpirationTime)) {
        $jwtExpirationTime = new \DateTime("now +2 days");
    }

    $payload = array_merge($data, [
        "iat" => $jwtIssueTime->getTimeStamp(),
        "nbf" => $jwtValidTime->getTimeStamp(),
        "exp" => $jwtExpirationTime->getTimeStamp(),
    ]);

    return Firebase\JWT\JWT::encode($payload, $key, $alg);
}

/**
 * Decodes jwt
 *
 * @param string $jwt
 * @param array $alg
 *
 * @return bool
 */
function decodeJWT($jwt, $alg = ["HS256"]) {
    // JWT secret
    $key = getenv("JWT_SECRET");

    return Firebase\JWT\JWT::decode($jwt, $key, $alg);
}

/**
 * Check if the countryCode is valid
 *
 * @param string $countryCode
 *
 * @return bool
 */
function isValidCountryCode($countryCode) {
    return in_array($countryCode, array_keys(getCountryCodeNameMapper()));
}

/**
 * Get an associatiave array of country codes and their full names
 *
 * @return array
 */
function getCountryCodeNameMapper() {
    return [
        'AF' => 'Afghanistan',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas, The',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BR' => 'Brazil',
        'BN' => 'Brunei',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'MM' => 'Burma',
        'BI' => 'Burundi',
        'CV' => 'Cabo Verde',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo (Brazzaville)',
        'CD' => 'Congo (Kinshasa)',
        'CR' => 'Costa Rica',
        'CI' => 'Cote dIvoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GO' => 'Gabon',
        'GM' => 'Gambia, The',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GR' => 'Greece',
        'GD' => 'Grenada',
        'GT' => 'Guatemala',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'VA' => 'Holy See',
        'HN' => 'Honduras',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, North',
        'KR' => 'Korea, South',
        'XK' => 'Kosovo',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States of',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'QA' => 'Qatar',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syria',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
        'TW' => 'Taiwan',
        'HK' => 'Hong Kong',
    ];
}
