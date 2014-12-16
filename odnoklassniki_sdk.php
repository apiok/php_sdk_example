<?php
class OdnoklassnikiSDK{
    const PARAMETER_NAME_ACCESS_TOKEN = "access_token";
    const PARAMETER_NAME_REFRESH_TOKEN = "refresh_token";
    private static $app_id = "";
    private static $app_public_key = "";
    private static $app_secret_key = "";
    private static $redirect_url = "";
    private static $scope = "VALUABLE_ACCESS";
    private static $TOKEN_SERVICE_ADDRESS = "http://api.odnoklassniki.ru/oauth/token.do";
    private static $API_REQUSET_ADDRESS = "http://api.odnoklassniki.ru/fb.do";
    private static $access_token;
    private static $refresh_token;
    
    public static function getAuthorizeUrl() {
        return sprintf("http://www.odnoklassniki.ru/oauth/authorize?client_id=%s&scope=%s&response_type=code&redirect_uri=%s",
                        urlencode(self::$app_id),
                        urlencode(self::$scope),
                        urlencode(self::$redirect_url));
    } 
    
    public static function getAppId(){
        return self::$app_id;
    }
    
    public static function getRedirectUrl(){
        return self::$redirect_url;
    }
    
    public static function getCode(){
        if (!empty($_GET["code"])) {
            return $_GET["code"];
        }
        else {
            return null;
        }
    }
    
    public static function checkCurlSupport(){
        return function_exists('curl_init');
    }
    
    public static function changeCodeToToken($code){
        $curl = curl_init(self::$TOKEN_SERVICE_ADDRESS);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'code=' . $code . '&redirect_uri=' . self::$redirect_url . '&grant_type=authorization_code&client_id=' . self::$app_id . '&client_secret=' . self::$app_secret_key);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $s = curl_exec($curl);
        curl_close($curl);
        $a = json_decode($s, true);
        if (!empty($a[self::PARAMETER_NAME_ACCESS_TOKEN])){
            self::$access_token = $a[self::PARAMETER_NAME_ACCESS_TOKEN];
        }
        if (!empty($a[self::PARAMETER_NAME_REFRESH_TOKEN])){
            self::$refresh_token = $a[self::PARAMETER_NAME_REFRESH_TOKEN];
        }
        return !empty($a[self::PARAMETER_NAME_ACCESS_TOKEN]) && !empty($a[self::PARAMETER_NAME_REFRESH_TOKEN]);
    }
    
    public static function updateAccessTokenWithRefreshToken(){
        $curl = curl_init(self::$TOKEN_SERVICE_ADDRESS);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'refresh_token=' . self::$refresh_token . '&grant_type=refresh_token&client_id=' . self::$app_id . '&client_secret=' . self::$app_secret_key);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $s = curl_exec($curl);
        curl_close($curl);
        $a = json_decode($s, true);
        if (empty($a[self::PARAMETER_NAME_ACCESS_TOKEN])) {
            return false;
        } else {
            self::$access_token = $a[self::PARAMETER_NAME_ACCESS_TOKEN];
            return true;
        }
    }
    
    public static function makeRequest($methodName, $parameters = null){
        if (is_null(self::$app_id) || is_null(self::$app_public_key) || is_null(self::$app_secret_key) || is_null(self::$access_token) || !(is_null($parameters) || is_array($parameters))){
            return null;
        }
        if (!is_null($parameters)) {
            if (!self::isAssoc($parameters)){
                return null;
            }
        } else {
            $parameters = array();
        }
        $parameters["application_key"] = self::$app_public_key;
        $parameters["method"] = $methodName;
        $parameters["sig"] = self::calcSignature($methodName, $parameters);
        $parameters[self::PARAMETER_NAME_ACCESS_TOKEN] = self::$access_token;
        $requestStr = "";
        foreach($parameters as $key=>$value){
            $requestStr .= $key . "=" . urlencode($value) . "&";
        }
        $requestStr = substr($requestStr, 0, -1);
        $curl = curl_init(self::$API_REQUSET_ADDRESS . "?" . $requestStr);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $s = curl_exec($curl);
        curl_close($curl);
        return json_decode($s, true);
    }
    
    private static function calcSignature($methodName, $parameters = null){
        if (is_null(self::$app_id) || is_null(self::$app_public_key) || is_null(self::$app_secret_key) || is_null(self::$access_token) || !(is_null($parameters) || is_array($parameters))){
            return null;
        }
        if (!is_null($parameters)) {
            if (!self::isAssoc($parameters)){
                return null;
            }
        } else {
            $parameters = array();
        }
        $parameters["application_key"] = self::$app_public_key;
        $parameters["method"] = $methodName;
        if (!ksort($parameters)){
            return null;
        } else {
            $requestStr = "";
            foreach($parameters as $key=>$value){
                $requestStr .= $key . "=" . $value;
            }
            $requestStr .= md5(self::$access_token . self::$app_secret_key);
            return md5($requestStr);
        }
    }
    
    private static function isAssoc($arr){
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
?>
