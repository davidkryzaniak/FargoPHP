<html>
<pre>
<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

    //Get the class
    require_once('FargoPHP/FargoPHP.class.php');


    //Create a new FargoPHP object.
    //Takes in the IP address (or DNS/Domain name) of the Fargo. Do not include the "http://" or the ending /
    //Username, Password
    $myFargo = new FargoPHP('192.168.1.34','admin','Kryzaniak12345');

    //if you have DNS setup, you can use
    //Again, Do not include the "http://" or the ending /
    //$myFargo = new FargoPHP('myfargodns.dyndns.com','admin','admin');

    /*
     * Remember, relays are numbered differently
     * Fargo Relay #1 = 0
     * Fargo Relay #2 = 1
     * Fargo Relay #3 = 2
     * etc...
     */

    //shutoff all relays
    $myFargo->setAllTo(FALSE);

    //change the state of relay #1
    $myFargo->relayFlipState(1); //#1 is now ON

    //change the state of relay #0
    echo "Relay #0 is currently: ".($myFargo->relayFlipState(0) ? 'On' : 'Off'); //#0 is now ON

    echo "\n\r";//line break

    echo "Relay #1 is currently: ".($myFargo->relayFlipState(1) ? 'On' : 'Off'); //#1 is now OFF

    echo "\n\r";//line break

    echo "Relay #0 is currently: ".($myFargo->relayFlipState(0) ? 'On' : 'Off'); //#0 is now OFF

    echo "\n\r";//line break

    //turn on the odd numbered relays
    $myFargo->relayFlipState(1);
    $myFargo->relayFlipState(3);
    $myFargo->relayFlipState(5);
    $myFargo->relayFlipState(7);

    //make sure #7 is really on
    $myFargo->setRelayState(7,TRUE);

    //get an array of the relays and their states
    print_r($myFargo->getAllRelayStates());

    echo "\n\r";//line break

    //try to turn ON #8 (which doesn't exist on an R8)
    echo "Relay #8 is currently: ".$myFargo->relayFlipState(8);

    echo "\n\r";//line break

    //That's all for this example! Turn off all the relays
    $myFargo->setAllTo(FALSE);

    echo "Current Temperature: ".$myFargo->getTemp();

    //close the connection to the Fargo
    $myFargo = NULL;

?>
</pre>
</html>