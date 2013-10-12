<html>
<pre>
<?php

    //turn on debug mode
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once('FargoPHP/FargoPHP.class.php');

    $myFargo = new FargoPHP('192.168.1.34','admin','Kryzaniak12345');

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

    //try to turn ON #8 (which doesn't exist on an R8)
    echo "Relay #8 is currently: ".$myFargo->relayFlipState(8);

    //That's all for this example! Turn off all the relays
    $myFargo->setAllTo(FALSE);

    //close the connection to the Fargo
    $myFargo = NULL;

?>
</pre>
</html>