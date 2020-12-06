<?php

class rex_cr
{

    public $rest_url = "https://rest.cleverreach.com";
    public $auth_url;
    public $token_url;

    private  $access_token;
    private  $refresh_token;

    public $clientid;
    private $clientsecret;

    private $redirect_uri;


    public function __construct()
    {
        $this->auth_url  = $this->rest_url . "/oauth/authorize.php";
        $this->token_url = $this->rest_url . "/oauth/token.php";

        $this->access_token = rex_config::get('cleverreach', 'access_token');
        $this->refresh_token = rex_config::get('cleverreach', 'refresh_token');

        $this->clientid = rex_config::get('cleverreach', 'clientid');
        $this->clientsecret = rex_config::get('cleverreach', 'clientsecret');

        $this->redirect_uri = trim(rex::getServer(),'/') . rex_getUrl('','',['func'=>'crnewtoken','clientid'=>$this->clientid],'&');

    }


    public function get_api() {
        $rest = new \CR\tools\rest($this->rest_url);
        $rest->error = false;
        $rest->throwExceptions = false;
        $rest->setAuthMode('bearer', $this->access_token);
        return $rest;
    }

    /**
     * is_available
     */

    public function is_available()
    {
        $rest = $this->get_api();
        return $rest->get('/v3/debug/validate');
    }

    /**
     * refresh_token
     */
    public function refresh_token()
    {

        // Values from your OAuth app.
        $authorization = base64_encode($this->clientid . ":" . $this->clientsecret);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->token_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array("grant_type" => "refresh_token", "refresh_token" => $this->refresh_token));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $authorization));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($res, true);

        if (isset($result['access_token'])) {
            foreach ($result as $k => $v) {
                rex_config::set('cleverreach', $k, $v);
            }
            return true;
        }
        return false;
    }

    /**
     * get_oauth_link
     */
    public function get_oauth_link () {
        $rdu = urlencode($this->redirect_uri);
        return "<a href=\"{$this->auth_url}?client_id={$this->clientid}&grant=basic&response_type=code&redirect_uri={$rdu}\" target=\"_blank\">OAuth Login bei Cleverreach generieren.</a>";      

    }


    /**
     * new_access_token
     */

    public function new_access_token()
    {

        if (!isset($_GET["code"])) { // no code, show start page
            return;
        } else {  // code, callback from OAuth

            // Trade the code for a Token
            $fields["client_id"] = $this->clientid;
            $fields["client_secret"] = $this->clientsecret;
            $fields["redirect_uri"] = $this->redirect_uri;
            $fields["grant_type"] = "authorization_code";
            $fields["code"] = $_GET["code"];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->token_url);
            curl_setopt($curl, CURLOPT_POST, sizeof($fields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($res, true);

            // Make sure to store the access_token in order to control the users REST API!
            if (isset($result['access_token'])) {
                foreach ($result as $k => $v) {
                    rex_config::set('cleverreach', $k, $v);
                }
                $return = true;
            }
        }
        return $return;
    }
}
