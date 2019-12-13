<?php
$host = 'mtext.local'; //host
$port = '9000'; //port
$null = NULL; //null var
//Create TCP/IP stream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
//bind socket to specified host
socket_bind($socket, 0, $port);
//listen to port
socket_listen($socket);
//create & add listning socket to the list
$clients = array($socket);
// text state saved on server
$editor_text = '';
//start endless loop, so that our script doesn't stop
while (true) {
    //manage multiple connections
    $changed = $clients;
    //returns the socket resources in $changed array
    socket_select($changed, $null, $null, 0, 10);

    //check for new socket
    if (in_array($socket, $changed)) {
        $socket_new = socket_accept($socket); //accept new socket
        $clients[] = $socket_new; //add socket to client array

        $header = socket_read($socket_new, 1024); //read data sent by the socket
        perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake

        socket_getpeername($socket_new, $ip); //get ip address of connected socket
        $response = mask(json_encode(array('type' => 'system', 'data' => array( 'ip' =>$ip, 'action' => 'connect') ))); //prepare json data
        send_message($response); //notify all users about new connection

        //make room for new socket
        $found_socket = array_search($socket, $changed);
        unset($changed[$found_socket]);
    }

    //loop through all connected sockets
    foreach ($changed as $changed_socket) {

        //check for any incomming data
        while(socket_recv($changed_socket, $buf, 8192, 0) >= 1)
        {
            $received_text = unmask($buf); //unmask data
            var_dump($received_text);
            $client_msg = json_decode($received_text, true); //json decode
            var_dump($client_msg);
            $type = $client_msg['type']; //message type
            $data = $client_msg['data']; //message data
            $response = [];
            $send_filter = [];
            $send_exclude = [];

            switch ($type) {
                case 'load':
                    $response = array('type'=>'load', 'data'=> $editor_text);
                    // only requesting client gets file
                    $send_filter[] = $changed_socket;
                    break;
                case 'diff':
                    apply_diff($data);
                    $response = array('type'=>'patch', 'data'=> $data);
                    // dont send patch to client that have changes already
                    $send_exclude[] = $changed_socket;
                    break;
            }
            //prepare data to be sent to client
            $response_text = mask(json_encode($response));
            send_message($response_text, $send_filter, $send_exclude); //send data
            break 2; //exit foreach
        }

        $buf = @socket_read($changed_socket, 8192, PHP_NORMAL_READ);
        if ($buf === false) { // check disconnected client
            // remove client for $clients array
            $found_socket = array_search($changed_socket, $clients);
            socket_getpeername($changed_socket, $ip);
            unset($clients[$found_socket]);

            //notify all users about disconnected connection
            $response = mask(json_encode(array('type'=>'system', 'data' => array( 'ip' =>$ip, 'action' => 'disconnect') )));
            send_message($response);
        }
    }
}

function apply_diff($diffs) {
    global $editor_text;
    foreach ($diffs as $element) {
        $type = $element['type'];
        $index = $element['idx'];
        $diff = $element['diff'];
        switch ($type) {
            case 'add':
                $editor_text = substr_replace($editor_text, $diff, $index, 0);
                break;
            case 'remove':
                $substr = substr($editor_text, $index, strlen($diff));
                if ($substr == $diff) {
                    $editor_text = substr($editor_text, 0, $index) . substr($editor_text, $index + strlen($diff));
                }
                break;
        }
    }
}

function send_message($msg, $filter = array(), $exclude = array())
{
    global $clients;
    foreach($clients as $changed_socket)
    {
        if ($filter && in_array($changed_socket, $filter) || $exclude && !in_array($changed_socket, $exclude)) {
            @socket_write($changed_socket, $msg, strlen($msg));
        }
    }
    return true;
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

// handshake new client
function perform_handshaking($received_header,$client_conn, $host, $port)
{
    $headers = array();
    $lines = preg_split("/\r\n/", $received_header);
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
        "WebSocket-Location: ws://$host:$port/websocket\r\n".
        "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn,$upgrade,strlen($upgrade));
}