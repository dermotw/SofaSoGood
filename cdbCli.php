#!/usr/bin/php
<?php
/**
 * cdbCli
 *
 * Useful tool that demonstrates SofaSoGood using, whilst also providing
 * a method for interacting with CouchDB databases from the CLI.
 * @package sofasogood
 * @author Dermot Williams <dermot@deadlocked.org>
 */
/**
 * SofaSoGood CoucDB Class
 */
require( 'sofasogood.class.php' );

declare( ticks = 1 );
pcntl_signal( SIGTERM, "signals" );

$theUser = NULL;
$thePass = NULL;

/**
 * Call the main() program loop
 */
main();

/**
 * main 
 * The main program loop
 *
 */
function main() {
	// This is our input loop; we keep trying to read a line from STDIN
	//
	// Open STDIN for reading
	//
	$fp = fopen('php://stdin', 'r');
	do {
		// Print a prompt...
		//
		echo '> ';
		// Read a line from STDIN and remove the trailing new-line
		//
		$cdbInput = fgets( $fp, 4096 );
		$cdbInput = preg_replace( '/\n/', '', $cdbInput );

		// Implement the exit/quit command
		//
		if ( $cdbInput == 'exit' || $cdbInput == 'quit' ) { exit; }

		// Implement the user/password mechanism
		//
		if ( preg_match( '/^user (.*)$/', $cdbInput, $userInfo ) ) {
			// Disable terminal echoing so that we don't display the password
			//
			`stty -echo`;
			$theUser = $userInfo[1];
			print "Password: ";
			$thePass = fgets( $fp, 4069 );
			$thePass = preg_replace( '/\n/', '', $thePass );
			$cdbInput = '';

			// Re-enable terminal echoing
			//
			`stty echo`;
			print "\n";
		}
		// If the content of cdbInput hasn't been cleared, then we pass it
		// to doCdb for parsing
		//
		if( $cdbInput ) { doCdb( $cdbInput, $theUser, $thePass ); }
		$cdbInput = '';
	} while ( true );
	fclose( $fp );

}

function signals( $sig ) {
	switch( $sig ) {
		case SIGTERM:
			print "Received SIGTERM, exiting!\n";
			exit;
			break;
	}
}

/**
 * doCdb 
 * Parse the command line and call the appropriate sofasogood method
 *
 * @param cdbInput string
 * @param theUser string
 * @param thePass string
 */
function doCdb( $cdbInput, $theUser, $thePass ) {
	$theCmd = NULL; $theURL = NULL; $theData = NULL;

	// Stanza for parsing 'CMD URL'
	//
	if ( preg_match( '/^([a-zA-Z]+) (\S+)$/', $cdbInput, $cmdArray ) ) {
		$theCmd = strtoupper( $cmdArray[1] );
		$theURL = $cmdArray[2];
	
	// Stanza for parsing 'CMD URL DATA'
	//
	} elseif ( preg_match( '/^([a-zA-Z]+) (\S+) (\S+)$/', $cdbInput, $cmdArray ) ) {
		$theCmd = strtoupper( $cmdArray[1] );
		$theURL = $cmdArray[2];
		$theData = $cmdArray[3];
	}

	// Create an instance of a sofasogood object
	//
	$cdbConn = new sofasogood();
	$cdbConn->cdbUser = $theUser;
	$cdbConn->cdbPass = $thePass;

	// Execute the command by passing the URL and DATA (if any) to the appropriate method
	//
	switch( $theCmd ) {
		case 'PUT':
			$cdbResponse = $cdbConn->cdbPut( $theURL, $theData );
			break;
		case 'GET':
			$cdbResponse = $cdbConn->cdbGet( $theURL, $theData );
			break;
		case 'DELETE':
			print "Deleting...\n";
			$cdbResponse = $cdbConn->cdbDelete( $theURL, $theData );
			break;
		case 'HEAD':
			$cdbResponse = $cdbConn->cdbHead( $theURL, $theData );
			break;
		case 'COMPACT':
			$cdbResponse = $cdbConn->cdbCompact( $theURL );
			break;

		// Catch-all case that prints a helpful message if we don't understand
		// what the user has typed
		//
		default:
			$cdbResponse = "Wait, what?\n";
			break;
	}

	print_r( $cdbResponse );

}

?>
