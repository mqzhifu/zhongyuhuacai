<?php
class WebSocketClientLib {
    private $client;
    private $state;
    private $host;
    private $port;
    private $handler;
    private $buffer;
    private $openCb;
    private $messageCb;
    private $closeCb;

    const HANDSHAKING = 1;
    const HANDSHAKED = 2;


    const WEBSOCKET_OPCODE_CONTINUATION_FRAME = 0x0;
    const WEBSOCKET_OPCODE_TEXT_FRAME = 0x1;
    const WEBSOCKET_OPCODE_BINARY_FRAME = 0x2;
    const WEBSOCKET_OPCODE_CONNECTION_CLOSE = 0x8;
    const WEBSOCKET_OPCODE_PING = 0x9;
    const WEBSOCKET_OPCODE_PONG = 0xa;

    const TOKEN_LENGHT = 16;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on("connect", [$this, "onConnect"]);
        $this->client->on("receive", [$this, "onReceive"]);
        $this->client->on("close", [$this, "onClose"]);
        $this->client->on("error", [$this, "onError"]);
        $this->handler = new PacketHandler();
        $this->buffer = "";
    }

    public function connect($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->client->connect($host, $port);
    }

    public function sendHandShake()
    {
        $this->state = static::HANDSHAKING;
        $request = $this->handler->buildHandShakeRequest($this->host, $this->port);
        $this->client->send($request);
    }

    public function onConnect($cli)
    {
        $this->sendHandShake();
    }

    public function onReceive($cli, $data)
    {
        if ($this->state == static::HANDSHAKING) {
            $this->buffer .= $data;
            $pos = strpos($this->buffer, "\r\n\r\n", true);

            if ($pos != false) {
                $header = substr($this->buffer, 0, $pos + 4);
                $this->buffer = substr($this->buffer, $pos + 4);

                if (true == $this->handler->verifyUpgrade($header)) {
                    $this->state = static::HANDSHAKED;
                    if (isset($this->openCb))
                        call_user_func($this->openCb, $this);
                } else {
                    echo "handshake failed\n";
                }
            }
        } else if ($this->state == static::HANDSHAKED) {
            $this->buffer .= $data;
        }
        if ($this->state == static::HANDSHAKED) {
            try {
                $frame = $this->handler->processDataFrame($this->buffer);
            } catch (\Exception $e) {
                $cli->close();
                return;
            }
            if ($frame != null) {
                if (isset($this->messageCb))
                    call_user_func($this->messageCb, $this, $frame);
            }
        }

    }

    public function onClose($cli)
    {
        if (isset($this->closeCb))
            call_user_func($this->closeCb, $this);
    }

    public function onError($cli)
    {
        echo "error occurred\n";
    }

    public function on($event, $callback)
    {
        if (strcasecmp($event, "open") === 0) {
            $this->openCb = $callback;
        } else if (strcasecmp($event, "message") === 0) {
            $this->messageCb = $callback;
        } else if (strcasecmp($event, "close") === 0) {
            $this->closeCb = $callback;
        } else {
            echo "$event is not supported\n";
        }
    }

    public function send($data, $type = 'text')
    {
        switch($type)
        {
            case 'text':
                $_type = self::WEBSOCKET_OPCODE_TEXT_FRAME;
                break;
            case 'binary':
            case 'bin':
                $_type = self::WEBSOCKET_OPCODE_BINARY_FRAME;
                break;
            case 'ping':
                $_type = self::WEBSOCKET_OPCODE_PING;
                break;
            case 'close':
                $_type = self::WEBSOCKET_OPCODE_CONNECTION_CLOSE;
                break;

            case 'ping':
                $_type = self::WEBSOCKET_OPCODE_PING;
                break;

            case 'pong':
                $_type = self::WEBSOCKET_OPCODE_PONG;
                break;

            default:
                echo "$type is not supported\n";
                return;
        }
        $data = \swoole_websocket_server::pack($data, $_type);
        $this->client->send($data);
    }

    public function getTcpClient()
    {
        return $this->client;
    }
}

class WebSocketFrame {
    public $finish;
    public $opcode;
    public $data;
}

class PacketHandler {
    const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    const TOKEN_LENGHT = 16;
    const maxPacketSize = 2000000;
    private $key = "";

    private static function generateToken($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';

        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);

        return base64_encode($randomString);
    }

    public function buildHandShakeRequest($host, $port)
    {
        $this->key = static::generateToken(self::TOKEN_LENGHT);

        return "GET / HTTP/1.1" . "\r\n" .
            "Origin: null" . "\r\n" .
            "Host: {$host}:{$port}" . "\r\n" .
            "Sec-WebSocket-Key: {$this->key}" . "\r\n" .
            "User-Agent: SwooleWebsocketClient"."/0.1.4" . "\r\n" .
            "Upgrade: Websocket" . "\r\n" .
            "Connection: Upgrade" . "\r\n" .
            "Sec-WebSocket-Protocol: wamp" . "\r\n" .
            "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }

    public function verifyUpgrade($packet)
    {
        $headers = explode("\r\n", $packet);
        unset($headers[0]);
        $headerInfo = [];
        foreach ($headers as $header) {
            $arr = explode(":", $header);
            if (count($arr) == 2) {
                list($field, $value) = $arr;
                $headerInfo[trim($field)] = trim($value);
            }
        }

        return (isset($headerInfo['Sec-WebSocket-Accept']) && $headerInfo['Sec-WebSocket-Accept'] == base64_encode(pack('H*', sha1($this->key.self::GUID))));
    }

    public function processDataFrame(&$packet)
    {
        if (strlen($packet) < 2)
            return null;
        $header = substr($packet, 0, 2);
        $index = 0;

        //fin:1 rsv1:1 rsv2:1 rsv3:1 opcode:4
        $handle = ord($packet[$index]);
        $finish = ($handle >> 7) & 0x1;
        $rsv1 = ($handle >> 6) & 0x1;
        $rsv2 = ($handle >> 5) & 0x1;
        $rsv3 = ($handle >> 4) & 0x1;
        $opcode = $handle & 0xf;
        $index++;

        //mask:1 length:7
        $handle = ord($packet[$index]);
        $mask = ($handle >> 7) & 0x1;

        //0-125
        $length = $handle & 0x7f;
        $index++;
        //126 short
        if ($length == 0x7e)
        {
            if (strlen($packet) < $index + 2)
                return null;
            //2 byte
            $handle = unpack('nl', substr($packet, $index, 2));
            $index += 2;
            $length = $handle['l'];
        }
        //127 int64
        elseif ($length > 0x7e)
        {
            if (strlen($packet) < $index + 8)
                return null;
            //8 byte
            $handle = unpack('Nh/Nl', substr($packet, $index, 8));
            $index += 8;
            $length = $handle['l'];
            if ($length > static::maxPacketSize)
            {
                throw new \Exception("frame length is too big.\n");
            }
        }

        //mask-key: int32
        if ($mask)
        {
            if (strlen($packet) < $index + 4)
                return null;
            $mask = array_map('ord', str_split(substr($packet, $index, 4)));
            $index += 4;
        }

        if (strlen($packet) < $index + $length)
            return null;
        $data = substr($packet, $index, $length);
        $index += $length;

        $packet = substr($packet, $index);

        $frame = new WebSocketFrame;
        $frame->finish = $finish;
        $frame->opcode = $opcode;
        $frame->data = $data;
        return $frame;
    }
}