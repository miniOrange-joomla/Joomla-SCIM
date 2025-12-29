<?php
/**    You should have received a copy of the GNU General Public License
*    	along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange scim
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
*This class contains all the utility functions

**/
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class MoSCIMUtility{

	public static function is_customer_registered()
    {
        $result = self::getCustomerDetails();

        $email 			= isset($result['email']) ? $result['email'] : '';
        $customerKey 	= isset($result['customer_key']) ? $result['customer_key'] : 0;
        $status = isset($result['registration_status']) ? $result['registration_status'] : '';

        if($email && $status == 'SUCCESS'){
            return 1;
        } else{
            return 0;
        }
    }

	public static function getCustomerDetails()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_scim_customer'));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        return $db->loadAssoc();
    }
	
	public static function mo_scim_check_empty_or_null($value) 
	{
		if (!isset($value) || empty($value)) {
            return true;
        }
        return false;
	}
	
	public static function mo_scim_is_curl_installed() 
	{
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else 
			return 0;
	}
	
	public static function mo_scim_get_hostname(){
		return 'https://login.xecurify.com';
	}	
	
	public static function GetPluginVersion()
    {
        $db = Factory::getDbo();
        $dbQuery = $db->getQuery(true)
            ->select('manifest_cache')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_scim'));
        $db->setQuery($dbQuery);
        $manifest = json_decode($db->loadResult());
        return($manifest->version);
    }

	public static function getJoomlaCmsVersion()
    {
        $jVersion   = new Version;
        return($jVersion->getShortVersion());
    }
	
	public static function getServerType()
    {
        $server = $_SERVER['SERVER_SOFTWARE'] ?? '';
        if (stripos($server, 'Apache') !== false) {
            return 'Apache';
        }
        if (stripos($server, 'nginx') !== false) {
            return 'Nginx';
        }
        if (stripos($server, 'LiteSpeed') !== false) {
            return 'LiteSpeed';
        }
        if (stripos($server, 'IIS') !== false) {
            return 'IIS';
        }
        return 'Unknown';
    }
		
	public static function mo_scim_encrypt($str) {
		if(!self::mo_scim_is_extension_installed('openssl')) {
			return;
		}
		
		$key=99189 ;
		return base64_encode(openssl_encrypt($str, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));	
	}
		
	public static function mo_scim_decrypt($value)
	{
		if(!self::mo_scim_is_extension_installed('openssl')) {
			return;
		}
		$key=99189 ;
		$string=rtrim( openssl_decrypt(base64_decode($value), 'aes-128-ecb', $key, OPENSSL_RAW_DATA), "\0");
		return trim($string,"\0..\32");
	}
		
	
	public static function mo_scim_is_extension_installed($extension_name)
	{
		if  (in_array  ($extension_name, get_loaded_extensions()))
			return 1;
		else
			return 0;
	}
	
	public static function mo_scim_get_joomla_groups(){

		$db=Factory::getDbo();
		$db->setQuery($db->getQuery(true)
			->select('*')
			->from("#__usergroups")
			->where($db->quoteName('title').'!='.$db->quote('Super Users').'AND'.$db->quoteName('title').'!='.$db->quote('Public').'AND'.$db->quoteName('title').'!='.$db->quote('Guest'))
		);
		return $db->loadRowList();
	}

	public static function moscimUpdateData($tableName,$tableFields,$tableConditions){
		
		$db=Factory::getDbo();
		$query=$db->getQuery(true);
		$sanFields=array();
		foreach ($tableFields as $key=>$value){
			array_push($sanFields,$db->quoteName($key) . '=' . $db->quote($value));
		}

		$sanConditions=array();
		foreach ($tableConditions as $key=>$value){
			array_push($sanConditions,$db->quoteName($key) . '=' . $db->quote($value));
		}
		$query->update($db->quoteName($tableName))->set($sanFields)->where($sanConditions);
		$db->setQuery($query);
		$db->execute();

	}

	public static function moscimFetchData($tableName,$condition=TRUE,$method='loadAssoc',$columns='*'){

		$db=Factory::getDbo();
		$query=$db->getQuery(true);
		$columns=is_array($columns)?$db->quoteName($columns):$columns;
		$query->select($columns);
		$query->from($db->quoteName($tableName));
        if($condition!==TRUE)
        {
            foreach ($condition as $key=>$value)
                $query->where($db->quoteName($key) . "=" . $db->quote($value));
        }

		$db->setQuery($query);
		if ($method=='loadColumn')
			return $db->loadColumn();
		else if($method=='loadObjectList')
			return $db->loadObjectList();
        else if($method=='loadObject')
            return $db->loadObject();
		else if($method=='loadResult')
			return $db->loadResult();
		else if($method=='loadRow')
			return $db->loadRow();
        else if($method=='loadRowList')
            return $db->loadRowList();
        else if($method=='loadAssocList')
            return $db->loadAssocList();
		else
			return $db->loadAssoc();
	}

	public function load_database_values($table)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName($table));
        $query->where($db->quoteName('id') . " = 1");
        $db->setQuery($query);
        $default_config = $db->loadAssoc();
        return $default_config;
    }

	public static function loadDBValues($table, $load_by, $col_name = '*', $id_name = 'id', $id_value = 1){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($col_name);

        $query->from($db->quoteName($table));
        if(is_numeric($id_value)){
            $query->where($db->quoteName($id_name)." = $id_value");

        }else{
            $query->where($db->quoteName($id_name) . " = " . $db->quote($id_value));
        }
        $db->setQuery($query);

        if($load_by == 'loadAssoc'){
            $default_config = $db->loadAssoc();
        }
        elseif ($load_by == 'loadResult'){
            $default_config = $db->loadResult();
        }
        elseif($load_by == 'loadColumn'){
            $default_config = $db->loadColumn();
        }
        return $default_config;
    }

	public static function generic_update_query($database_name, $updatefieldsarray){

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        foreach ($updatefieldsarray as $key => $value)
        {
            $database_fileds[] = $db->quoteName($key) . ' = ' . $db->quote($value);
        }
        $query->update($db->quoteName($database_name))->set($database_fileds)->where($db->quoteName('id')." = 1");
        $db->setQuery($query);
        $db->execute();
    }

	public static function send_installation_mail($fromEmail, $content)
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $customer_details = (new MoSCIMUtility)->load_database_values('#__miniorange_scim_customer');
        $customerKey = !empty($customer_details['customer_key']) ? $customer_details['customer_key'] : '16555';
        $apiKey = !empty($customer_details['api_key']) ? $customer_details['api_key'] : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        $currentTimeInMillis = round(microtime(true) * 1000);
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
        $hashValue = hash("sha512", $stringToHash);
        $headers = [
            "Content-Type: application/json",
            "Customer-Key: $customerKey",
            "Timestamp: $currentTimeInMillis",
            "Authorization: $hashValue"
        ];
        $fields = [
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => [
                'customerKey' => $customerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'nutan.barad@xecurify.com',
                'bccEmail' => 'pritee.shinde@xecurify.com',
                'subject' => 'Installation of SCIM User Provisioning [Free] Plugin',
                'content' => '<div>' . $content . '</div>',
            ],
        ];
        $field_string = json_encode($fields);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = 'SendMail CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return json_encode(['status' => 'error', 'message' => $errorMsg]);
        }
        curl_close($ch);
        return $response;
    }
	
	public static function mo_scim_get_operating_system()
	{
	
		if (isset($_SERVER)) {
			$user_agent=$_SERVER['HTTP_USER_AGENT'];
		} else {
			global $HTTP_SERVER_VARS;
			if (isset($HTTP_SERVER_VARS)) {
				$user_agent=$HTTP_SERVER_VARS['HTTP_USER_AGENT'];
			} else {
				global $HTTP_USER_AGENT;
				$user_agent=$HTTP_USER_AGENT;
			}
		}
	
		$os_array=[
			'windows nt 10'=> 'Windows 10',
			'windows nt 6.3'=> 'Windows 8.1',
			'windows nt 6.2'=> 'Windows 8',
			'windows nt 6.1|windows nt 7.0'=> 'Windows 7',
			'windows nt 6.0'=> 'Windows Vista',
			'windows nt 5.2'=> 'Windows Server 2003/XP x64',
			'windows nt 5.1'=> 'Windows XP',
			'windows xp'=> 'Windows XP',
			'windows nt 5.0|windows nt5.1|windows 2000'=> 'Windows 2000',
			'windows me'=> 'Windows ME',
			'windows nt 4.0|winnt4.0'=> 'Windows NT',
			'windows ce'=> 'Windows CE',
			'windows 98|win98'=> 'Windows 98',
			'windows 95|win95'=> 'Windows 95',
			'win16'=> 'Windows 3.11',
			'mac os x 10.1[^0-9]'=> 'Mac OS X Puma',
			'macintosh|mac os x'=> 'Mac OS X',
			'mac_powerpc'=> 'Mac OS 9',
			'linux'=> 'Linux',
			'ubuntu'=> 'Linux - Ubuntu',
			'iphone'=> 'iPhone',
			'ipod'=> 'iPod',
			'ipad'=> 'iPad',
			'android'=> 'Android',
			'blackberry'=> 'BlackBerry',
			'webos'=> 'Mobile',
	
			'(media center pc).([0-9]{1,2}\.[0-9]{1,2})'=> 'Windows Media Center',
			'(win)([0-9]{1,2}\.[0-9x]{1,2})'=> 'Windows',
			'(win)([0-9]{2})'=> 'Windows',
			'(windows)([0-9x]{2})'=> 'Windows',
			'Win 9x 4.90'=> 'Windows ME',
			'(windows)([0-9]{1,2}\.[0-9]{1,2})'=> 'Windows',
			'win32'=> 'Windows',
			'(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})'=> 'Java',
			'(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}'=> 'Solaris',
			'dos x86'=> 'DOS',
			'Mac OS X'=> 'Mac OS X',
			'Mac_PowerPC'=> 'Macintosh PowerPC',
			'(mac|Macintosh)'=> 'Mac OS',
			'(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'=> 'SunOS',
			'(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}'=> 'BeOS',
			'(risc os)([0-9]{1,2}\.[0-9]{1,2})'=> 'RISC OS',
			'unix'=> 'Unix',
			'os/2'=> 'OS/2',
			'freebsd'=> 'FreeBSD',
			'openbsd'=> 'OpenBSD',
			'netbsd'=> 'NetBSD',
			'irix'=> 'IRIX',
			'plan9'=> 'Plan9',
			'osf'=> 'OSF',
			'aix'=> 'AIX',
			'GNU Hurd'=> 'GNU Hurd',
			'(fedora)'=> 'Linux - Fedora',
			'(kubuntu)'=> 'Linux - Kubuntu',
			'(ubuntu)'=> 'Linux - Ubuntu',
			'(debian)'=> 'Linux - Debian',
			'(CentOS)'=> 'Linux - CentOS',
			'(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'=> 'Linux - Mandriva',
			'(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)'=> 'Linux - SUSE',
			'(Dropline)'=> 'Linux - Slackware (Dropline GNOME)',
			'(ASPLinux)'=> 'Linux - ASPLinux',
			'(Red Hat)'=> 'Linux - Red Hat',
			'(linux)'=> 'Linux',
			'(amigaos)([0-9]{1,2}\.[0-9]{1,2})'=> 'AmigaOS',
			'amiga-aweb'=> 'AmigaOS',
			'amiga'=> 'Amiga',
			'AvantGo'=> 'PalmOS',
			'[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})'=> 'Linux',
			'(webtv)/([0-9]{1,2}\.[0-9]{1,2})'=> 'WebTV',
			'Dreamcast'=> 'Dreamcast OS',
			'GetRight'=> 'Windows',
			'go!zilla'=> 'Windows',
			'gozilla'=> 'Windows',
			'gulliver'=> 'Windows',
			'ia archiver'=> 'Windows',
			'NetPositive'=> 'Windows',
			'mass downloader'=> 'Windows',
			'microsoft'=> 'Windows',
			'offline explorer'=> 'Windows',
			'teleport'=> 'Windows',
			'web downloader'=> 'Windows',
			'webcapture'=> 'Windows',
			'webcollage'=> 'Windows',
			'webcopier'=> 'Windows',
			'webstripper'=> 'Windows',
			'webzip'=> 'Windows',
			'wget'=> 'Windows',
			'Java'=> 'Unknown',
			'flashget'=> 'Windows',
			'MS FrontPage'=> 'Windows',
			'(msproxy)/([0-9]{1,2}.[0-9]{1,2})'=> 'Windows',
			'(msie)([0-9]{1,2}.[0-9]{1,2})'=> 'Windows',
			'libwww-perl'=> 'Unix',
			'UP.Browser'=> 'Windows CE',
			'NetAnts'=> 'Windows',
		];
	
		$arch_regex='/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
		$arch=preg_match($arch_regex, $user_agent) ? '64' : '32';
	
		foreach ($os_array as $regex=> $value) {
			if (preg_match('{\b(' . $regex . ')\b}i', $user_agent)) {
				return $value . ' x' . $arch;
			}
		}
	
		return 'Unknown';
	}

	
	public static function convertBinaryToString($input) {
		if (is_string($input) && !mb_detect_encoding($input, 'utf-8', true)) {
			// Convert binary to base64
			return base64_encode($input);
		}
		return $input;
	}

	public static function exportData($tableNames)
    {
        $db = Factory::getDbo();
        $jsonData = [];

        if (empty($tableNames)) {
            $jsonData['error'] = 'No table names provided.';
        } else {
            foreach ($tableNames as $tableName) {
                $query = $db->getQuery(true);
                $query->select('*')
                      ->from($db->quoteName($tableName));

                $db->setQuery($query);
                try {
                    $data = $db->loadObjectList();
                    
                    if (empty($data)) {
                        $jsonData[$tableName] = ['message' => 'This table is empty.'];
                    } else {
                        $jsonData[$tableName] = $data;
                    }
                } catch (Exception $e) {
                    $jsonData[$tableName] = ['error' => $e->getMessage()];
                }
            }
        }

        header('Content-disposition: attachment; filename=exported_data.json');
        header('Content-type: application/json');
        echo json_encode($jsonData, JSON_PRETTY_PRINT);

        Factory::getApplication()->close();
    }
}
?>