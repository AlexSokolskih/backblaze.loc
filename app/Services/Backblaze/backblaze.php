<?php
/**
 * Created by PhpStorm.
 * User: sokolskih
 * Date: 17.03.2018
 * Time: 13:33
 */

namespace App\Services\Backblaze;


use Illuminate\Support\Facades\Storage;

class Backblaze
{
    private $authorizationToken ='';
    private $apiUrl ='';
    private $downloadUrl ='';
    private $account_id ='';
    private $bucketName ='';

    public function __construct()
    {
        $this->authorizationToken=env('BACKBLAZE_authorizationToken');
        $this->apiUrl=env('BACKBLAZE_apiUrl');
        $this->downloadUrl=env('BACKBLAZE_downloadUrl');
        $this->account_id=env('BACKBLAZE_ACCOUNT_ID');
        $this->bucketName=config('backblaze.bucketName');
    }

    private function getRequest($data, $url)
    {

        $session = curl_init($url);

// Add post fields
        $post_fields = json_encode($data);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields);

// Add headers

        $headers = array();
        $headers[] = "Authorization: " . $this->authorizationToken;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  // Receive server response
        $server_output = curl_exec($session); // Let's do this!
        curl_close ($session); // Clean up
        return $server_output;
    }
    
    public function getMasterToken()
    {
        $account_id = env('BACKBLAZE_ACCOUNT_ID'); // Obtained from your B2 account page
        $application_key = env('BACKBLAZE_APPLICATION_KEY'); // Obtained from your B2 account page
        $credentials = base64_encode($account_id . ":" . $application_key);
        $url = "https://api.backblazeb2.com/b2api/v1/b2_authorize_account";

        $session = curl_init($url);

// Add headers
        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: Basic " . $credentials;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);  // Add headers

        curl_setopt($session, CURLOPT_HTTPGET, true);  // HTTP GET
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true); // Receive server response
        $server_output = curl_exec($session);
        curl_close ($session);
        var_dump(json_decode($server_output));
    }

    public function list_buckets()
    {
        $data = array("accountId" => $this->account_id);
        $method = 'b2_list_buckets';
        $url = $this->apiUrl .  "/b2api/v1/".$method;
        $server_output = $this->getRequest($data, $url);
        return json_decode($server_output)->buckets; // Tell me about the rabbits, George!
    }

    public function list_file_names($bucket_id)
    {
        $data = array("bucketId" => $bucket_id);
        $method = 'b2_list_file_names';
        $url = $this->apiUrl .  "/b2api/v1/".$method;
        $server_output = $this->getRequest($data, $url);
        return json_decode($server_output)->files; // Tell me about the rabbits, George!
    }

    public function download_file_by_name($file_name)
    {
        $download_url = $this->downloadUrl; // From b2_authorize_account call
        $bucket_name =$this->bucketName;  // The NAME of the bucket you want to download from
        $file_name =  urlencode($file_name); // The name of the file you want to download
        $auth_token = $this->authorizationToken; // From b2_authorize_account call
        $uri = $download_url . "/file/" . $bucket_name . "/" . $file_name;

       //var_dump($uri);
        $session = curl_init($uri);

// Add headers
        $headers = array();
        $headers[] = "Authorization: " . $auth_token;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_HTTPGET, true); // HTTP POST
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  // Receive server response
        $server_output = curl_exec($session); // Let's do this!
        curl_close ($session); // Clean up
        //readfile($server_output); // Tell me about the rabbits, George!
        Storage::put(urldecode($file_name), $server_output);
        return response()->file('C:\1\OSPanel\domains\backblaze.loc\storage\app\\'.urldecode($file_name));
    }

}