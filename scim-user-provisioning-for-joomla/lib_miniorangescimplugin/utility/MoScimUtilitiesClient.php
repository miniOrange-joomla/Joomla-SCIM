<?php
/**
 * @package     Joomla.Library
 * @subpackage  library.miniorangescimplugin
 *
 * @copyright   Copyright 2015 miniOrange. All Rights Reserved.
 * @license     http://miniorange.com/usecases/miniOrange_User_Agreement.pdf
 */
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Version;

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_scim' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
class MoScimUtilitiesClient
{
    static function mo_log($log_msg)
    {
        $filePath = $_SERVER['DOCUMENT_ROOT']."/log/log.log";
        $sizeInBytes = filesize($filePath);

        // Convert byte to kb upto 2 decimal
        $sizeInKb = number_format($sizeInBytes / 1024, 2);


        if($sizeInKb >= 256)
        {
            //Clean the file if the size is greater than or equal to 256kb
            file_put_contents($filePath, "");
        }

        $log_filename = $_SERVER['DOCUMENT_ROOT']."/log";
        if (!file_exists($log_filename))
        {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($filePath, var_export($log_msg, true). "\n", FILE_APPEND);
    }

    public static function create_bearer_token(){
        $bearer_token = bin2hex(random_bytes(32));
        self::insert_token_into_table($bearer_token);
        return $bearer_token;
    }


    public static function insert_token_into_table($bearer_token){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('bearer_token') . ' = ' . $db->quote($bearer_token),
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = 1',
        );

        $query->update($db->quoteName('#__miniorange_scim_details'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }


    static function customCombineArray($keys, $values)
    {
        $combinedArray = array();
        foreach ($keys as $key => $value) {
            if (trim($value) == "" || trim($values[$key]) == "")
                continue;
            $combinedArray[$value] = $values[$key];
        }
        return $combinedArray;
    }

    public static function get_scim_config($appName = NULL)
    {
        $condition = is_null($appName) ? array('id' => 1) : array('appname' => $appName);
        return self::miniScimFetchDb('#__miniorange_scim_details', $condition);
    }


    public static function saveSCIMConfig($db_name,$column,$value)
    {
      MoScimUtilitiesClient::mo_log('save_config');
        $updatefieldsarray = array(
            $column => isset($value) ? $value : false,
        );
        self::updateDBValues($db_name, $updatefieldsarray);
    }

 

  
  public static function keepRecords($status,$task){
      $details       = self::getCustomerDetails();
      $dVar=new JConfig();
      $check_email = $dVar->mailfrom;
      $admin_email = !empty($details ['email']) ? $details ['email'] :$check_email;
      $admin_phone = isset($details ['admin_phone']) ? $details ['admin_phone'] : '';
      self::saveSCIMConfig('#__miniorange_scim_customer','email', $admin_email);
      MoScimUtilitiesClient::mo_log('keepRecords');
      $contact_us = new MoScimCustomer();
      $contact_us->submit_feedback_form($admin_email, $admin_phone,$status,$task);
  }

    public static function getCustomerDetails()
    {
        return self::miniScimFetchDb('#__miniorange_scim_customer', array('id' => 1));
    }

    public static function check($val)
    {
        if (empty($val))
            return "";
        else
            return self::decrypt($val);
    }

    public static function getJoomlaCmsVersion()
    {
        $jVersion   = new Version;
        return($jVersion->getShortVersion());
    }
    
    public static function getCustomerKeys($isMiniorange = false)
    {
        $keys = array();
        if ($isMiniorange) {
            $keys['customer_key'] = "16555";
            $keys['apiKey'] = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

        } else {
            $details = self::getCustomerDetails();
            $keys['customer_key'] = $details['customer_key'];
            $keys['apiKey'] = $details['api_key'];
        }
        return $keys;
    }

    public static function encrypt($str)
    {
        if (!self::is_extension_installed('openssl')) {
            return;
        }
        $key = self::miniScimFetchDb('#__miniorange_scim_customer', array('id' => 1), 'loadResult', 'customer_token');
        return base64_encode(openssl_encrypt(stripcslashes($str), 'aes-128-ecb', $key, OPENSSL_RAW_DATA));
    }

    public static function decrypt($value)
    {
        if (!self::is_extension_installed('openssl')) {
            return;
        }

        $key = self::miniScimFetchDb('#__miniorange_scim_customer', array('id' => 1), 'loadResult', 'customer_token');

        $string = rtrim(openssl_decrypt(base64_decode($value), 'aes-128-ecb', $key, OPENSSL_RAW_DATA), "\0");
        return trim($string, "\0..\32");
    }

    public static function getHostname()
    {
        return 'https://login.xecurify.com';
    }

    public static function isCustomerRegistered($result = FALSE)
    {
        if ($result === FALSE)
            $result = self::getCustomerDetails();
        $email = $result['email'];
        $customerKey = $result['customer_key'];
        $status = $result['registration_status'];
        if ($email && $customerKey && is_numeric(trim($customerKey)) && $status == 'SUCCESS') {
            return 1;
        } else {
            return 0;
        }
    }

    static function miniScimFetchDb($tableName, $condition = TRUE, $method = 'loadAssoc', $columns = '*')
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $columns = is_array($columns) ? $db->quoteName($columns) : $columns;
        $query->select($columns);
        $query->from($db->quoteName($tableName));
        if ($condition !== TRUE) {
            foreach ($condition as $key => $value)
                $query->where($db->quoteName($key) . " = " . $db->quote($value));
        }

        $db->setQuery($query);
        return $db->$method();
    }

    static function miniScimUpdateDb($tableName, $fields, $conditions)
    {
        if (isset($fields['activation'])) {
            if (is_bool($fields['activation']))
                $fields['activation'] = ($fields['activation'] == TRUE ? 0 : 1);
            else
                $fields['activation'] = 1 - $fields['activation'];
        }
        if (isset($fields['block'])) {
            if (is_bool($fields['block']))
                $fields['block'] = ($fields['block'] == TRUE ? 0 : 1);
            else
                $fields['block'] = 1 - $fields['block'];
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        // Fields to update.
        $sanFields = array();
        foreach ($fields as $key => $value) {
            array_push($sanFields, $db->quoteName($key) . ' = ' . $db->quote($value));
        }
        // Conditions for which records should be updated.
        $sanConditions = array();
        foreach ($conditions as $key => $value) {
            array_push($sanConditions, $db->quoteName($key) . ' = ' . $db->quote($value));
        }
        $query->update($db->quoteName($tableName))->set($sanFields)->where($sanConditions);
        $db->setQuery($query);
        $db->execute();
    }

    /* php utilities */
    public static function check_empty_or_null($value)
    {
        if (!isset($value) || empty($value)) {
            return true;
        }
        return false;
    }

    public static function is_extension_installed($name)
    {
        if (in_array($name, get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    /**/
    static function get_timestamp()
    {

        $currentTimeInMillis = round(microtime(true) * 1000);
        $currentTimeInMillis = number_format($currentTimeInMillis, 0, '', '');

        return $currentTimeInMillis;
    }

    static function make_curl_call($url, $fields, $http_header_array = array('Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic'))
    {

        if (gettype($fields) !== 'string') {
            $fields = json_encode($fields);
        }

        $response = self::mo_post_curl($url, $fields, $http_header_array);
        return $response;

    }

    static function get_http_header_array($isMiniOrange = false)
    {
        $customerKeys = self::getCustomerKeys($isMiniOrange);
        $customerKey = $customerKeys['customer_key'];
        $apiKey = $customerKeys['apiKey'];

        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = self::get_timestamp();

        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;;
        $hashValue = hash("sha512", $stringToHash);

        $headers = array(
            "Content-Type: application/json",
            "Customer-Key: " . $customerKey,
            "Timestamp: " . $currentTimeInMillis,
            "Authorization: " . $hashValue
        );

        return $headers;
    }

    public static function mo_post_curl($url, $fields, $http_header_array)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header_array);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);
        return $content;
    }

    public static function is_curl_installed()
    {
        return (in_array('curl', get_loaded_extensions())) ? 1 : 0;
    }

    public static function check_customer($email)
    {
        if (!self::is_curl_installed()) {
            return json_encode(array("status" => 'CURL_ERROR', 'statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $hostname = self::getHostname();
        $url = $hostname . "/moas/rest/customer/check-if-exists";
        $ch = curl_init($url);

        $fields = array(
            'email' => $email,
        );

        $field_string = json_encode($fields);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);

        return $content;
    }

    public static function updateDBValues($database_name, $updatefieldsarray)
    {

        $db = Factory::getDbo();

        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value) {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }

        $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        $db->execute();
        MoScimUtilitiesClient::mo_log('update database');
    }

    public static function GetPluginVersion()
    {

        $manifest = json_decode(self::miniScimFetchDb('#__extensions', array('element' => 'com_miniorange_scim'), 'loadResult', 'manifest_cache'));

        return ($manifest->version);
    }

    static function getUserJson()
    {
        return '{
  "id": "urn:ietf:params:scim:schemas:core:2.0:User",
  "name": "User",
  "description": "User Schema",
  "attributes": [
    {
      "name": "userName",
      "type": "string",
      "multiValued": false,
      "required": true,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "server",
      "description": "Unique identifier for the User, typically used by the user to directly authenticate to the service provider. Each User MUST include a non-empty userName value. This identifier MUST be unique across the service provider\'s entire set of Users."
    },
    {
      "name": "name",
      "type": "complex",
      "multiValued": false,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "The components of the user\'s real name. Providers MAY return just the full name as a single string in the formatted sub-attribute, or they MAY return just the individual component attributes using the other sub-attributes, or they MAY return both.  If both variants are returned, they SHOULD be describing the same name, with the formatted name indicating how the component attributes should be combined.",
      "subAttributes": [
        {
          "name": "formatted",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The full name, including all middle names, titles, and suffixes as appropriate, formatted for display (e.g., \'Ms. Barbara J Jensen, III\')."
        },
        {
          "name": "familyName",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The family name of the User, or last name in most Western languages (e.g., \'Jensen\' given the full name \'Ms. Barbara J Jensen, III\')."
        },
        {
          "name": "givenName",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The given name of the User, or first name in most Western languages (e.g., \'Barbara\' given the full name \'Ms. Barbara J Jensen, III\')."
        },
        {
          "name": "middleName",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The middle name(s) of the User (e.g., \'Jane\' given the full name \'Ms. Barbara J Jensen, III\')."
        },
        {
          "name": "honorificPrefix",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The honorific prefix(es) of the User, or title in most Western languages (e.g., \'Ms.\' given the full name \'Ms. Barbara J Jensen, III\')."
        },
        {
          "name": "honorificSuffix",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The honorific suffix(es) of the User, or suffix in most Western languages (e.g., \'III\' given the full name \'Ms. Barbara J Jensen, III\')."
        }
      ]
    },
    {
      "name": "displayName",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "The name of the User, suitable for display to end-users.  The name SHOULD be the full name of the User being described, if known."
    },
    {
      "name": "nickName",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "The casual way to address the user in real life, e.g., \'Bob\' or \'Bobby\' instead of \'Robert\'.  This attribute SHOULD NOT be used to represent a User\'s username (e.g., \'bjensen\' or \'mpepperidge\')."
    },
    {
      "name": "profileUrl",
      "type": "reference",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "referenceTypes": [
        "external"
      ],
      "description": "A fully qualified URL pointing to a page representing the User\'s online profile."
    },
    {
      "name": "title",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "The user\'s title, such as \'Vice President.\'"
    },
    {
      "name": "userType",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Used to identify the relationship between the organization and the user. Typical values used might be \'Contractor\', \'Employee\', \'Intern\', \'Temp\', \'External\', and \'Unknown\', but any value may be used."
    },
    {
      "name": "preferredLanguage",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Indicates the User\'s preferred written or spoken language.  Generally used for selecting a localized user interface; e.g., \'en_US\' specifies the language English and country US."
    },
    {
      "name": "locale",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Used to indicate the User\'s default location for purposes of localizing items such as currency, date time format, or numerical representations."
    },
    {
      "name": "timezone",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "The User\'s time zone in the \'Olson\' time zone database format, e.g., \'America/Los_Angeles\'."
    },
    {
      "name": "active",
      "type": "boolean",
      "multiValued": false,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "A Boolean value indicating the User\'s administrative status."
    },
    {
      "name": "password",
      "type": "string",
      "multiValued": false,
      "required": false,
      "caseExact": false,
      "mutability": "writeOnly",
      "returned": "never",
      "uniqueness": "none",
      "description": "The User\'s cleartext password. This attribute is intended to be used as a means to specify an initial password when creating a new User or to reset an existing User\'s password."
    },
    {
      "name": "emails",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Email addresses for the user. The value SHOULD be canonicalized by the service provider, e.g., \'bjensen@example.com\' instead of \'bjensen@EXAMPLE.COM\'.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "Email addresses for the user. The value SHOULD be canonicalized by the service provider, e.g., \'bjensen@example.com\' instead of \'bjensen@EXAMPLE.COM\'."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "work",
            "home",
            "other"
          ],
          "description": "A label indicating the attribute\'s function, e.g., \'work\' or \'home\'."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address.  The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "phoneNumbers",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Phone numbers for the User. The value SHOULD be canonicalized by the service provider according to the format specified in RFC 3966, e.g., \'tel:+1-201-555-0123\'.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "Phone number of the User."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "work",
            "home",
            "mobile",
            "fax",
            "pager",
            "other"
          ],
          "description": "A label indicating the attribute\'s function, e.g., \'work\', \'home\', \'mobile\'."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "ims",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "Instant messaging addresses for the User.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "Instant messaging address for the User."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "aim",
            "gtalk",
            "icq",
            "xmpp",
            "msn",
            "skype",
            "qq",
            "yahoo"
          ],
          "description": "A label indicating the attribute\'s function, e.g., \'aim\', \'gtalk\', \'xmpp\'."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "photos",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "URLs of photos of the User.",
      "subAttributes": [
        {
          "name": "value",
          "type": "reference",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "referenceTypes": [
            "external"
          ],
          "description": "URLs of a photo of the User."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "photo",
            "thumbnail"
          ],
          "description": "A label indicating the attribute\'s function, i.e., \'photo\' or \'thumbnail\'."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "addresses",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "A physical mailing address for this User.",
      "subAttributes": [
        {
          "name": "formatted",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The full mailing address, formatted for display or use with a mailing label. This attribute MAY contain newlines."
        },
        {
          "name": "streetAddress",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The full street address component, which may include house number, street name, P.O. box, and multi-line extended street address information. This attribute MAY contain newlines."
        },
        {
          "name": "locality",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The city or locality component."
        },
        {
          "name": "region",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The state or region component."
        },
        {
          "name": "postalCode",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The zip code or postal code component."
        },
        {
          "name": "country",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The country name component."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "work",
            "home",
            "other"
          ],
          "description": "A label indicating the attribute\'s function, e.g., \'work\' or \'home\'."
        }
      ]
    },
    {
      "name": "groups",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readOnly",
      "returned": "default",
      "uniqueness": "none",
      "description": "A list of groups to which the user belongs, either through direct membership, through nested groups, or dynamically calculated.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readOnly",
          "returned": "default",
          "uniqueness": "none",
          "description": "The identifier of the User\'s group."
        },
        {
          "name": "$ref",
          "type": "reference",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readOnly",
          "returned": "default",
          "uniqueness": "none",
          "referenceTypes": [
            "Group"
          ],
          "description": "The URI of the corresponding \'Group\' resource to which the user belongs."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readOnly",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "canonicalValues": [
            "direct",
            "indirect"
          ],
          "description": "A label indicating the attribute\'s function, e.g., \'direct\' or \'indirect\'."
        }
      ]
    },
    {
      "name": "entitlements",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "A list of entitlements for the User that represent a thing the User has.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The value of an entitlement."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A label indicating the attribute\'s function."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "roles",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "A list of roles for the User that collectively represent who the User is, e.g., \'Student\', \'Faculty\'.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The value of a role."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A label indicating the attribute\'s function."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    },
    {
      "name": "x509Certificates",
      "type": "complex",
      "multiValued": true,
      "required": false,
      "mutability": "readWrite",
      "returned": "default",
      "uniqueness": "none",
      "description": "A list of certificates issued to the User.",
      "subAttributes": [
        {
          "name": "value",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "The value of an X.509 certificate."
        },
        {
          "name": "display",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A human-readable name, primarily used for display purposes."
        },
        {
          "name": "type",
          "type": "string",
          "multiValued": false,
          "required": false,
          "caseExact": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A label indicating the attribute\'s function."
        },
        {
          "name": "primary",
          "type": "boolean",
          "multiValued": false,
          "required": false,
          "mutability": "readWrite",
          "returned": "default",
          "uniqueness": "none",
          "description": "A Boolean value indicating the \'primary\' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value \'true\' MUST appear no more than once."
        }
      ]
    }
  ],
  "meta": {
    "resourceType": "Schema",
    "location": "https://wdc.test.host/scim/v2/Schemas/urn:ietf:params:scim:schemas:core:2.0:User"
  }
}';
    }

    public static function getAllAttributesOfUserSchema()
    {
        $userSchema = json_decode(MoScimUtilitiesClient::getUserJson());
        $attributes = array();
        foreach ($userSchema->attributes as $key => $value) {
            if ($value->type == "complex") {
                $subAttributes = array();
                foreach ($value->subAttributes as $key1 => $value1) {

                    if (!$value->multiValued) {
                        array_push($attributes, ($value->name) . '.' . ($value1->name));
                    } else {
                        if ($value1->name == "type") {
                            if (!isset($value1->canonicalValues)) {
                                $value1->canonicalValues[0] = "true";
                                $value1->name = "primary";
                            }
                            foreach ($value1->canonicalValues as $canValue) {
                                foreach ($subAttributes as $sub)
                                    array_push($attributes, $value->name . '[' . $value1->name . ' eq "' . $canValue . '"]' . '.' . $sub);
                            }
                            break;

                        } else if ($value1->type != "boolean") {
                            array_push($subAttributes, $value1->name);
                        }

                    }


                }

            } else
                array_push($attributes, $value->name);

        }
        return $attributes;


    }

    static function loadGroups()
    {
        return self::miniScimFetchDb("#__usergroups", TRUE, "loadAssocList");
    }
}