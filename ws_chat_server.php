<?php
set_time_limit(0);
error_reporting(E_ALL);

$host = '0.0.0.0';
$port = 8080;

$server = stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);
if (!$server) {
    echo "Error: {$errstr} ({$errno})\n";
    exit(1);
}

stream_set_blocking($server, false);
$clients = [];

function encode_message($payload) {
    $len = strlen($payload);
    $head = chr(0x81);
    if ($len <= 125) {
        $head .= chr($len);
    } elseif ($len <= 65535) {
        $head .= chr(126) . pack('n', $len);
    } else {
        $head .= chr(127) . pack('J', $len);
    }
    return $head . $payload;
}

function decode_message($data) {
    $len = ord($data[1]) & 127;
    if ($len === 126) {
        $masks = substr($data, 4, 4);
        $payload = substr($data, 8);
    } elseif ($len === 127) {
        $masks = substr($data, 10, 4);
        $payload = substr($data, 14);
    } else {
        $masks = substr($data, 2, 4);
        $payload = substr($data, 6);
    }
    $text = '';
    $l = strlen($payload);
    for ($i = 0; $i < $l; $i++) {
        $text .= $payload[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function perform_handshake($client, $headers) {
    if (!preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match)) {
        return false;
    }
    $key = trim($match[1]);
    $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    $upgrade =
        "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";
    fwrite($client, $upgrade);
    return true;
}

echo "WebSocket chat server running on {$host}:{$port}\n";

while (true) {
    $read = [$server];
    foreach ($clients as $c) {
        $read[] = $c['socket'];
    }
    $write = null;
    $except = null;

    if (stream_select($read, $write, $except, 1) > 0) {
        if (in_array($server, $read, true)) {
            $client = @stream_socket_accept($server, 0);
            if ($client) {
                stream_set_blocking($client, false);
                $clients[(int)$client] = [
                    'socket' => $client,
                    'handshaked' => false
                ];
            }
            $read = array_diff($read, [$server]);
        }

        foreach ($read as $sock) {
            $id = (int)$sock;
            $data = @fread($sock, 2048);
            if ($data === '' || $data === false) {
                if (feof($sock)) {
                    fclose($sock);
                    unset($clients[$id]);
                }
                continue;
            }

            if (!$clients[$id]['handshaked']) {
                if (perform_handshake($sock, $data)) {
                    $clients[$id]['handshaked'] = true;
                } else {
                    fclose($sock);
                    unset($clients[$id]);
                }
                continue;
            }

            $decoded = decode_message($data);
            $msg = @json_decode($decoded, true);
            if (!is_array($msg)) {
                continue;
            }

            if (($msg['type'] ?? '') === 'chat') {
                $payload = json_encode([
                    'type' => 'chat',
                    'order_id' => $msg['order_id'] ?? null,
                    'from_role' => $msg['from_role'] ?? null,
                    'text' => $msg['text'] ?? ''
                ]);
                $encoded = encode_message($payload);
                foreach ($clients as $cid => $cinfo) {
                    if ($cid === $id) {
                        continue;
                    }
                    @fwrite($cinfo['socket'], $encoded);
                }
            }
        }
    }
}
