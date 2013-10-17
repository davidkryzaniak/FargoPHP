FargoPHP - A simple PHP library to control your Fargo!
================================

See example.php for a quick setup guide. Tested with a Fargo R8.

Class Methods
================================

*__construct( String $url, String $username, String $password, [String $protocol = 'http://'])*
Constructor. Makes the initial connection to the Fargo.
Parameters:
String $url - The URL to the Fargo (like 192.168.1.32 or myfargo.dns-service.com)
String $username - Your username
String $password - Your password
String $protocol - Optional. Use this if you want to connect over ssl.

*array getAllRelayStates( )*
Creates an array of every relay with it's state. 1 = On, 0 = Off

*array getAllSystemInformation( )*
return - all the system data

*string getRelayState( [int $relay_numbered = 0])*
Returns the state of a given relay
return - 1 = On, 0 = Off, or an out of bounds alert
Parameters:
int $relay_numbered - between 0 and 3 (if its R4) or 7 (if its a R8)

*string getTemp( )*
return - Current temperature of the device (in celsius) like 33.0C

*string getTime( )*
return - Time that is set on the fargo Wed 13, 2013

*string getVolts( )*
return - Voltage being drawn. Like 10.98 VDC

*bool relayFlipState( [int $relay_numbered = 0])*
If the relay is ON, turn it OFF. If the relay is OFF, turn it ON.
return - 1 = On, 0 = Off, or an out of bounds alert
Parameters:
int $relay_numbered - Which relay you want to change

*void setAllTo( [bool $state = TRUE])*
Set all the relays to the same state
Parameters:
bool $state - TRUE = All On, FALSE = All Off

*bool setRelayState( int $relay_numbered, [bool $set_state = TRUE])*
Set a specific relay to a given state.
return - 1 = On, 0 = Off, or an out of bounds alert
Parameters:
int $relay_numbered - Number of the relay you want to change
bool $set_state - TRUE = All On, FALSE = All Off

*void setSleepTime( $micro_seconds $micro_seconds)*
Used for debugging. Sets the length of time between requests
Parameters:
$micro_seconds $micro_seconds - INT Number of microseconds to wait.
