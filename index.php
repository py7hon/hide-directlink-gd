<?php
#MIT License
#
#Copyright (c) 2019 Iqbal Rifai
#
#Permission is hereby granted, free of charge, to any person obtaining a copy
#of this software and associated documentation files (the "Software"), to deal
#in the Software without restriction, including without limitation the rights
#to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
#copies of the Software, and to permit persons to whom the Software is
#furnished to do so, subject to the following conditions:
#
#The above copyright notice and this permission notice shall be included in all
#copies or substantial portions of the Software.
#
#THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
#IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
#FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
#AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
#LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
#OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
#SOFTWARE.
error_reporting(0);
function get_http_response_code($redirect){
    $headers = get_headers($redirect);
    return substr($headers[0], 9, 3);
  }function my_simple_crypt($string, $action = 'e'){
    $secret_key     = ''; //your key
    $secret_iv      = ''; //your iv
    $output         = false;
    $encrypt_method = "AES-256-CBC";
    $key            = hash('sha256', $secret_key);
    $iv             = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'e'){
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
      }else if ($action == 'd'){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }
    return $output;
  }if ($_GET['id'] != ""){
    $id                     = $_GET['id'];
    $ori                    = my_simple_crypt($id, 'd');
    $apikey                 = ''; //your api key
    $url                    = "https://www.googleapis.com/drive/v2/files/$ori?supportsTeamDrives=true&key=$apikey";
    $redirect               = "https://www.googleapis.com/drive/v3/files/$ori?supportsTeamDrives=true&alt=media&key=$apikey";
    $json                   = file_get_contents($url);
    $data                   = json_decode($json, true);
    $get_http_response_code = get_http_response_code($redirect);
    $name                   = $data["title"];
    $mime                   = $data["mimeType"];
    if ($get_http_response_code == 403){
        header('Content-Type: application/json');
        http_response_code(403);
        $error = array(
        	'error' => true,
                'code' => 403,
                'reason' => 'downloadQuotaExceeded',
                'message' => 'The download quota for this file has been exceeded.'
            );
        echo json_encode($error);
      }else{
        header("Content-Type: $mime");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"$name\"");
        http_response_code(200);
        echo readfile($redirect);
      }
  }else{
    header('Content-Type: application/json');
    http_response_code(404);
    $error = array(
        "error" => true,
        "message" => "Missing id"
    );
    echo json_encode($error);
  }
?>
