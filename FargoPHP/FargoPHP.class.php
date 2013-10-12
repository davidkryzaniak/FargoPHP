<?php
/**
 * Created by JetBrains PhpStorm.
 * User: davidkryzaniak
 * Date: 11/10/2013
 * Time: 17:12
 * To change this template use File | Settings | File Templates.
 */

class FargoPHP {

    //My Fargo Info
    private $url;
    private $username;
    private $password;

    //based on info from your Fargo
    private $min_relay = 0;
    private $max_relay = 7;

    //constants for connecting with the fargo.
    private $protocol = 'http://';
    private $connect_uri = '/p/index.htm?init=1';
    private $change_relay_state = '/p/relays.cgi?state=';
    private $remove_success_msg = 'success! ';
    private $relay_status = '/p/relayStatus.xml';
    private $out_of_bounds_number = 'Out of Bounds! That number is either too high, or too low!';

    public function __construct($url,$username,$password,$protocol='http://')
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->protocol = $protocol;

        //just a test. Throws an error if not configured correctly.
        $this->request($this->url.$this->connect_uri);
    }

    public function __destruct()
    {
        $this->url = $this->username = $this->password = $this->connection = NULL;
    }

    public function relayFlipState($relay_numbered=0)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered])){
            return $this->request($this->url.$this->change_relay_state.$relay_numbered);
        }else
            return $this->out_of_bounds_number;
    }

    public function setRelayState($relay_numbered, $set_state=TRUE)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered])){
            if( ((boolean)$relays[$relay_numbered]) !== ((boolean)$set_state) )
                return $this->relayFlipState($relay_numbered);
        }else
            return $this->out_of_bounds_number;
    }

    public function setAllTo($state=TRUE)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        foreach($relays as $relay=>$status){
            $this->setRelayState($relay,$state);
        }

    }

    public function getRelayState($relay_numbered=0)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered]))
            return $relays[$relay_numbered];
        else
            return $this->out_of_bounds_number;
    }

    public function getAllRelayStates()
    {
        return $this->parseXMLAllRelayStatusToArray();
    }

    private function parseXMLAllRelayStatusToArray()
    {
        $xml = $this->request($this->protocol.$this->url.$this->relay_status,TRUE,TRUE);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values, $tags);
        xml_parser_free($parser);
        //Yikes! This is messy!
        return explode(',',$values[$tags['state'][0]]['value']);
    }

    /**
     * The heavy lifter of this class. Makes all the requests to the Fargo.
     *
     * @param string $url               Full URL to the fargo
     * @param bool $return_response     Return the full message usually "Success! 1"
     * @param bool $return_string       $return either a string or a boolean (boolean of the status)
     * @return mixed                    String of return data
     * @throws Exception                Fault on username or Password or Fargo not found
     */
    private function request($url,$return_response=FALSE,$return_string=FALSE)
    {
        //we don't want to overwhelm our Fargo (that's not a nice thing to do). Make Fargo take a nap to catch up!
        $this->nap();

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $response = curl_exec($curl);
        $resultStatus = curl_getinfo($curl);

        if($resultStatus['http_code'] == 401)
            throw new Exception('Authentication Error! Check Username/Password');

        if($resultStatus['http_code'] != 200)
            throw new Exception('Fargo not found :( Check your URL!');

        if($return_response&&!$return_string)
            return str_replace($this->remove_success_msg,'',strtolower($response));
        else
            return $response;
    }

    private function nap()
    {
        usleep(100);
    }

}