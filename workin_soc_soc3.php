<?php
error_reporting(~E_NOTICE);
set_time_limit (0);

$address = "127.0.0.1";
$port = '9000';
$max_clients = 10;
$null = NULL;
$sock = socket_create(AF_INET, SOCK_STREAM, 0)


socket_bind($sock, $address , $port)


socket_listen ($sock , 10)

$client_socks = array($sock);


while (true) 
{
	$read = $client_socks;
	
	socket_select($read , $null , $null , null) === false)
	
    if (in_array($sock, $read)) 
	{



                $sock_new = socket_accept($sock);
                
                $client_socks[] = $sock_new;	
                
                $header = socket_read($sock_new, 1024); //read data sent by the socket
				perform_handshaking($header, $sock_new, $address, $port); //perform websocket handshake
				$message ='';
                //display information about the client who is connected
				if(socket_getpeername($sock_new, $address, $port))
				{
					$message .= "Client $address : $port is now connected to us. \n";
				}
				
				//Send Welcome message to client
				$message .= "Welcome to php socket server version 1.0 \n";
				$message .= "Enter a message and press enter, and i shall reply back \n";

				send_msg(mask($message));
				
				
          
    }

    //check each client if they send any data
    foreach ($read as  $read_new) {
    
		if (in_array($read_new , $read))
		{
			$input = socket_read($read_new , 1024);
            $input = unmask($input);
            if ($input == null) 
			{
				//zero length string meaning disconnected, remove and close the socket
				unset($read_new);
				socket_close($read_new);
            }



            $output = "$input";
            
			
			//send response to client
			send_msg(mask($output));
		}
    }
}
socket_write($client, $response);
socket_close($client);

function send_msg($msg)
{	
	global $client_socks;
	foreach ($client_socks as $client) 
	{
		socket_write($client, $msg,strlen($msg));
	}
	return true;
}


//Unmask incoming framed message
function unmask($text) {
	
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//handshake new client.
function perform_handshaking($receved_header,$client_conn, $host, $port)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
		{
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/demo/shout.php\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}


?>