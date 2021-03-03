<?php

namespace DigitaleWerte\Wrapper;


class CheckMkApi
{
    private $username;
    private $password;
    private $baseurl;
    private $unsafeConnection=true;


    public function __construct($baseurl, $user, $password) {
        $this->username =$user;
        $this->password = $password;
        $this->baseurl = $baseurl;
    }

    /**
     * @param String $host
     * @param int $duration
     * @param String|null $starttime
     */
    public function createDowntime(String $host,String $comment, int $duration, ?String $starttime=null) {


        // setting our request parameter
        $request['method'] = 'GET';
        $request['uri'] = "/check_mk/view.py";
        $request['parameter'] = array(
            "_transid" => "-1",
            "_do_confirm" => "yes",
            "_do_actions" => "yes",
            "view_name" => "hoststatus",
            "host" => $host,
            "_down_comment" => urlencode($comment)
        );

        // first we will implement the method w
        $request['parameter']['_down_from_now'] = 'yes';
        $request['parameter']['_down_minutes'] = $duration;


        $data = $this->doRequest($request);


        /**
        &_down_custom=Custom+time+range
        &_down_from_date=2020-03-31
        &_down_from_time=12:30
        &_down_to_date=2020-03-31
        &_down_to_time=14:30
        &_down_comment=test_downtime
        &_down_from_now=yes
         */



    }

    /**
     * @param String $host
     * @param $dtid
     */
    public function deleteDowntime(String $host, $dtid = null) {

        $request['method'] = 'GET';
        $request['uri'] = "/check_mk/view.py";
        $request['parameter'] = array(
            "_transid" => "-1",
            "_do_confirm" => "yes",
            "_do_actions" => "yes",
            "view_name" => "hoststatus",
            "host" => $host,
            "_down_remove" => "Remove"
        );


        $data = $this->doRequest($request);



    }

    private function doRequest($reqParam) {

        $ch = curl_init();


        /**
         * The params we want to add to the URL
         */
        $getParams = '';

        $getParams .= '_username='.$this->username.'&';
        $getParams .= '_secret='.$this->password.'&';

        // Our Array for the headers we have to send
        $headers = array();

        //the parameters we get from the caller...
        if (isset($reqParam['parameter'])) {
            foreach($reqParam['parameter'] as $key=>$value) {
                $getParams .= $key.'='.$value.'&';
            }
            $getParams = trim($getParams, '&'); // we will delete & after the last param. Its more beautiful ;)
        }

        $curlopturl = $this->baseurl. $reqParam['uri'] . "?" . $getParams;

        curl_setopt($ch, CURLOPT_URL, $curlopturl);

        /**
         * Set the Request Type
         */
        switch ($reqParam['method']) {
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                if(isset($reqParam['postdata'])) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $reqParam['postdata']);
                    $headers[] = 'Content-Type: application/json';
                    //$headers[] = 'Content-Length:' . strlen($reqParam['postdata']);
                }

                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reqParam['data']));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reqParam['data']));
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }

        /**
         * Set unsafe Options...
         */
        if ($this->unsafeConnection) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }


        /**
         * Filling Headers
         */



        $headers[] = 'User-Agent: DW Services FortGate API Wrapper';
        $headers[] = 'Cache-Control: no-cache';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Der dafür da damit nicht 1 zurückgegeben wird.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);



        $server_output = curl_exec ($ch);

        curl_close ($ch);



        return  $server_output;


    }
}