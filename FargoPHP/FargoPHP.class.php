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
    private $tekDate = '/p/tekDate.xml';
    private $led = '/p/leds.cgi?led=';
    private $out_of_bounds_number = 'Out of Bounds! That number is either too high, or too low!';

    //General settings
    private $sleep_time = 100;


    /**
     * Constructor. Makes the initial connection to the Fargo.
     *
     * @param String $url The URL ro
     * @param String $username Your username
     * @param String $password Your password
     * @param String $protocol Optional. Use this if you want to connect over ssl.
     */
    public function __construct($url,$username,$password,$protocol='http://')
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->protocol = $protocol;
        $this->max_relay = count($this->getAllRelayStates());

        //just a test. Throws an error if not configured correctly.
        $this->request($this->url.$this->connect_uri);
    }

    /**
     * If the relay is ON, turn it OFF. If the relay is OFF, turn it ON.
     *
     * @param int $relay_numbered Which relay you want to change
     *
     * @return mixed|string 1 = On, 0 = Off, or an out of bounds alert
     */
    public function relayFlipState($relay_numbered=0)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered])){
            return $this->request($this->url.$this->change_relay_state.$relay_numbered);
        }else
            return $this->out_of_bounds_number;
    }

    /**
     * Set a specific relay to a given state.
     *
     * @param int $relay_numbered Number of the relay you want to change
     * @param bool $set_state TRUE = All On, FALSE = All Off
     *
     * @return mixed|string 1 = On, 0 = Off, or an out of bounds alert
     */
    public function setRelayState($relay_numbered, $set_state=TRUE)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered])){
            if( ((boolean)$relays[$relay_numbered]) !== ((boolean)$set_state) )
                return $this->relayFlipState($relay_numbered);
        }else
            return $this->out_of_bounds_number;
    }

    /**
     * Set all the relays to the same state
     *
     * @param bool $state TRUE = All On, FALSE = All Off
     */
    public function setAllTo($state=TRUE)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        foreach($relays as $relay=>$status){
            $this->setRelayState($relay,$state);
        }

    }

    /**
     * Returns the state of a given relay
     *
     * @param int $relay_numbered between 0 and 3 (if its R4) or 7 (if its a R8)
     *
     * @return string 1 = On, 0 = Off, or an out of bounds alert
     */
    public function getRelayState($relay_numbered=0)
    {
        $relays = $this->parseXMLAllRelayStatusToArray();
        if(isset($relays[$relay_numbered]))
            return $relays[$relay_numbered];
        else
            return $this->out_of_bounds_number;
    }

    /**
     * Creates an array of every relay with it's state. 1 = On, 0 = Off
     *
     * @return array Relays and their states
     */
    public function getAllRelayStates()
    {
        return $this->parseXMLAllRelayStatusToArray();
    }

    /**
     * @return array all the system data
     */
    public function getAllSystemInformation()
    {
        return $this->parseXMLAllRelayStatusToArray();
    }

    /**
     * @return string Time that is set on the fargo Wed 13, 2013
     */
    public function getTime()
    {
        $data = $this->parseXMLTekDateToArray();
        return $data['date'];
    }

    /**
     * @return string Voltage being drawn. Like 10.98 VDC
     */
    public function getVolts()
    {
        $data = $this->parseXMLTekDateToArray();
        return $data['volts'];
    }

    /**
     * @return string Current temperature of the device (in celsius) like 33.0C
     */
    public function getTemp()
    {
        $data = $this->parseXMLTekDateToArray();
        return $data['temperature'];
    }

    /**
     * Used for debugging. Sets the length of time between requests
     *
     * @param $micro_seconds INT Number of microseconds to wait.
     */
    public function setSleepTime($micro_seconds)
    {
        $this->sleep_time = $micro_seconds;
    }

    public function getLEDState($led_number)
    {
        //todo FINISH THIS!
    }

    /**
     * Creates an array of every relay with it's state. 1 = On, 0 = Off
     *
     * @return array Relays and their states
     */
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
     * Gets the system setting
     *
     * @return array
     */
    private function parseXMLTekDateToArray()
    {
        $xml = $this->request($this->protocol.$this->url.$this->tekDate,TRUE,TRUE);
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values, $tags);
        xml_parser_free($parser);
        unset($tags['response']);
        $returnArray = array();
        foreach($tags as $singleTag=>$value)
            $returnArray[$singleTag] = $values[$value[0]]['value'];
        return $returnArray;
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

    /**
     * Get the time to sleep
     */
    private function nap()
    {
        usleep($this->sleep_time);
    }

}