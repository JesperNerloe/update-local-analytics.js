<?php
	
	#
	#	MIT License
	#
	#	Copyright (c) 2016 Jesper Nerløe.
	#
	#	Permission is hereby granted, free of charge, to any person obtaining a copy
	#	of this software and associated documentation files (the "Software"), to deal
	#	in the Software without restriction, including without limitation the rights
	#	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	#	copies of the Software, and to permit persons to whom the Software is
	#	furnished to do so, subject to the following conditions:
	#
	#	The above copyright notice and this permission notice shall be included in all
	#	copies or substantial portions of the Software.
	#
	#	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	#	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	#	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	#	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	#	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	#	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	#	SOFTWARE.
	#
	
	#
	#	================================== READ THIS ==================================
	#
	#	This file:	analytics.php
	#
	#	Description:	This simple PHP script retrieves the remote analytics.js (Google Analytics 
	#			javascript) and saves it as a local file (default destination: the root 
	#			directory of your website.)
	#
	#	To do:		1) Replace the "CURLOPT_USERAGENT" value with your own user-agent name 
	#			   (line 131 in "CRAWLER CONFIGURATION").
	#			2) Replace "// ERROR HANDLING" at line 109 and 180 with error handling of 
	#			   your own.
	#			3) Replace "//www.google-analytics.com/analytics.js" with 
	#			   "//[your-domain].com/analytics.js" in the Google Analytics tracking code.
	#			4) Set up a cron job to run the script on daily basis to keep analytics.js 
	#			   updated and in place.
	#
	#	Bonus:		Now, go get that 100/100 score in Google PageSpeed Insights ;-)
	#
	#	GitHub:		https://github.com/JesperNerloe/
	#	Twitter:	https://twitter.com/JesperNerloe
	#	Google+:	https://plus.google.com/+JesperNerloe
	#
	
	##  ANALYTICS URL
	$url = 'http://www.google-analytics.com/analytics.js';
	
	
	##  GET ANALYTICS.JS
	$data = GET_ANALYTICS( $url );
	
	
	##  IF HTTP CODE IS "200"
	if( $data[ 'http_code' ] == 200 ) {
		
		
		##  DESTINATION FILE
		$destination_file = $_SERVER[ 'DOCUMENT_ROOT' ] . '/analytics.js';
		
		
		##  SET STATUS
		$update = false;
		
		
		##  IF DESTINATION FILE EXISTS
		if( file_exists( $destination_file ) ) {
			
			
			##  IF ANALYTICS.JS IS MORE RECENT THAN THE DESTINATION FILE
			if( $data[ 'filetime' ] > filemtime( $destination_file ) ) {
				
				
				##  DELETE CACHE STATE
				##  http://php.net/manual/en/function.clearstatcache.php
				clearstatcache();
				
				
				##  SET UPDATE STATUS
				$update = true;
			}
			
			
		##  ELSE (IF DESTINATION FILE DOESN'T EXIST)
		} else {
			$update = true;
		}
		
		
		##  IF UPDATE IS NEEDED
		if( $update === true ) {
			
			
			##  SAVE AS FILE
			file_put_contents( $destination_file, $data[ 'contents' ] );
		}
		
		
	##  ELSE (IF HTTP CODE IS NOT "200")
	} else {
		
		// ERROR HANDLING
		
	}
	
	
	##  CURL CRAWLER
	function GET_ANALYTICS( $url ) {
		
		
		##  SET HEADER
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";
		
		
		##  CRAWLER CONFIGURATION
		$options = [
			CURLOPT_URL			=> $url,
			CURLOPT_USERAGENT		=> '[INSERT USER-AGENT]',
			CURLOPT_HTTPHEADER		=> $header,
			CURLOPT_REFERER			=> '-',
			CURLOPT_ENCODING		=> 'gzip,deflate',
			CURLOPT_AUTOREFERER		=> true,
			CURLOPT_RETURNTRANSFER		=> 1,
			CURLOPT_SSL_VERIFYHOST		=> 0,
			CURLOPT_SSL_VERIFYPEER		=> 0,
			CURLOPT_TIMEOUT			=> 120,
			CURLOPT_CONNECTTIMEOUT		=> 120,
			CURLOPT_FOLLOWLOCATION		=> 1,
			CURLOPT_MAXREDIRS		=> 10,
			CURLOPT_VERBOSE			=> 1,
			CURLOPT_HEADER			=> 1,
			CURLOPT_NOBODY			=> 0,
			CURLOPT_FILETIME		=> true,
			CURLOPT_FORBID_REUSE		=> true,
			CURLOPT_FRESH_CONNECT		=> true
		];
		
		$curl					= curl_init();
		curl_setopt_array( $curl, $options );
		$results				= curl_exec( $curl );
		$err					= curl_errno( $curl );
		$err_msg				= curl_error( $curl );
		$info					= curl_getinfo( $curl );
		curl_close( $curl );
		
		
		##  SEPARATE "HEADERS" AND "CONTENTS"
		list( $headers, $contents ) = explode( "\r\n\r\n", $results, 2 );
		$data[ 'headers' ]			= $headers;
		$data[ 'contents' ]			= $contents;
		
		
		##  GET URL META
		$data[ 'http_code' ]			= $info[ 'http_code' ];
		$data[ 'file_time' ]			= $info[ 'filetime' ];
		
		
		##  CURL ERROR CODES
		$data[ 'error_code' ]			= $err;
		$data[ 'error_msg' ]			= $err_msg;
		
		
		##  IF CURL ERROR
		if( $data[ 'error_code' ] > 0) {
			
			// ERROR HANDLING
			
		}
		
		return $data;
	}
?>
