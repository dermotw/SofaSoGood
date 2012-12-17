<?php
/**
 * SofaSoGood CouchDB Class
 *
 * SofaSoGood is a simple class for interacting with CouchDB databases. It is not intended to be
 * comprehensive, but it is intended to be easy to use.
 * @package sofasogood
 * @author Dermot Williams <dermot@deadlocked.org>
 *
*/
/**
 * SofaSoGood
 *
 * The main class.
 * @package sofasogood
 */
class sofasogood {

	/** @var string */
	public $cdbUser;
	/** @var string */
	public $cdbPass;
	/** @var string */
	public $cdbHost = 'localhost';
	/** @var int */
	public $cdbPort = 5984;	

	private $cdbSock;

	/**
	 * cdbConnect
	 *
	 * Open a connection to the CouchDB server.
	 *
	 */
	private function cdbConnect() {
		$this->cdbSock = fsockopen( $this->cdbHost, $this->cdbPort, $errno, $errstr );
	}

	/**
	 * cdbClose
	 *
	 * Close the CouchDB connection once we're done with it.
	 *
	 */
	private function cdbClose() {
		fclose( $this->cdbSock );
	}

	/**
	 * cdbGet
	 *
	 * Retrieve data from the CouchDB database.
	 *
	 * @param cdbUrl string
	 * @param cdbData string
	 * @return cdbResponse string
	 *
	 */
	public function cdbGet( $cdbURL, $cdbData = NULL ) {
		$cdbResponse = $this->cdbExecute( 'GET', $cdbURL, $cdbData );
		return ( json_decode( $cdbResponse[1] ) );
	}

	/**
	 *
	 * cdbPut
	 *
	 * Put data into the CouchDB database.
	 *
	 * @param cdbURL string
	 * @param cdbData string
	 * @return cdbResponse string
	 *
	 */
	public function cdbPut( $cdbURL, $cdbData = NULL ) {
		$cdbResponse = $this->cdbExecute( 'PUT', $cdbURL, $cdbData );
		return ( json_decode( $cdbResponse[1] ) );
	}

	public function cdbDelete( $cdbUrl ) {
		$cdbResponse = $this->cdbExecute( 'DELETE', $cdbUrl, $cdbData );
		return( json_decode( $cdbResponse[1] ) );
	}

	/**
	 * cdbHead
	 *
	 * Execute a HEAD command. HEAD is used to retrieve info about the given deocument
	 *
	 * @param cdbUrl string The URL of the document
	 * @return cdbResponse string
	 */
	public function cdbHead ( $cdbUrl ) {
		$cdbResponse = $this->cdbExecute( 'HEAD', $cdbUrl, $cdbData );
		return( $cdbResponse[0] . "\n" );
	}

	/**
	 *
	 * cdbCompact
	 *
	 * Compact a given database.
	 *
	 * @param cdbUrl string The URL of the database
	 * @return cdbResponse string
	 */
	public function cdbCompact( $cdbUrl, $cdbData = NULL ) {
		$cdbResponse = $this->cdbExecute( 'POST', $cdbUrl . '/_compact', $cdbData );
		return( $cdbResponse[0] . "\n" );
	}

	/**
	 *
	 * cdbExecute
	 *
	 * Send the request to the CouchDB server.
	 *
	 * @param cdbMethod string
	 * @param cdbURL string
	 * @param cdbData string
	 * @return cdbResponse string
	 *
	 */
	private function cdbExecute( $cdbMethod, $cdbURL, $cdbData ) {
		$this->cdbConnect();
		$cdbRequest = "$cdbMethod $cdbURL HTTP/1.0\r\nHost: $this->cdbHost\r\n";
		if ( $this->cdbUser ) {
			$cdbRequest .= "Authorization: Basic ". base64_encode( "$this->cdbUser:$this->cdbPass") ."\r\n";
		}
		
		if ( $cdbData ) {
			$cdbRequest .= "Content-Length: ". strlen( $cdbData ) ."\r\n\r\n";
			$cdbRequest .= "$cdbData\r\n";
		} else {
			$cdbRequest .= "\r\n";
		}

		fwrite( $this->cdbSock, $cdbRequest );
		$cdbReturn = '';
		
		while ( !feof( $this->cdbSock ) ) {
			$cdbReturn .= fgets( $this->cdbSock );
		}

		$cdbResponse = explode( "\r\n\r\n", $cdbReturn );
		$this->cdbClose();
		return ( $cdbResponse );

	}
}

?>
