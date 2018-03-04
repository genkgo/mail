<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Stub;

use Genkgo\Mail\Protocol\ConnectionInterface;

final class FakeSmtpConnection implements ConnectionInterface
{
    public const STATE_NONE = 0;

    public const STATE_CONNECTED = 1;

    public const STATE_EHLO = 2;

    public const STATE_AUTHORIZING = 3;

    public const STATE_AUTHORIZED = 4;

    public const STATE_DATA_RECEIVING = 5;

    public const STATE_DATA_RECEIVED = 6;

    /**
     * @var array
     */
    private $advertisements = [];

    /**
     * @var string
     */
    private $greeting = '250-welcome to fake connection';

    /**
     * @var bool
     */
    private $secure = false;

    /**
     * @var array
     */
    private $listeners = ['connect' => []];

    /**
     * @var int
     */
    private $state = self::STATE_NONE;

    /**
     * @var array
     */
    private $buffer = [];

    /**
     * @var string
     */
    private $from = '';

    /**
     * @var array
     */
    private $to = [];

    /**
     * @var array
     */
    private $metaData = [];

    /**
     * @var bool
     */
    private $isLegacy = false;

    /**
     * @param array $advertisements
     */
    public function __construct(array $advertisements = ['250-STARTTLS', '250-AUTH PLAIN', '250 AUTH LOGIN'])
    {
        $this->advertisements = $advertisements;
        $this->reset();
    }

    /**
     * @return FakeSmtpConnection
     */
    public static function newLegacyRfc821(): self
    {
        $factory = new self();
        $factory->isLegacy = true;

        return $factory;
    }

    /**
     * @param string $name
     * @param \Closure $callback
     */
    public function addListener(string $name, \Closure $callback): void
    {
        $this->listeners[$name][] = $callback;
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        if ($this->state >= self::STATE_CONNECTED) {
            throw new \RuntimeException('Cannot connect while already connected');
        }

        $this->buffer = [$this->greeting];
        $this->state = self::STATE_CONNECTED;

        foreach ($this->listeners['connect'] as $listener) {
            $listener();
        }
    }

    /**
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->state === self::STATE_NONE) {
            throw new \RuntimeException('Cannot disconnect while not connected');
        }

        $this->reset();
    }

    /**
     * @param string $request
     * @return int
     */
    public function send(string $request): int
    {
        if ($this->state === self::STATE_NONE) {
            throw new \RuntimeException('Cannot communicate while not connected');
        }

        if (!empty($this->buffer)) {
            $this->buffer = ['554 Synchronization error'];
        }

        $request = \trim($request);
        $parameters = \explode(' ', $request);
        $command = \reset($parameters);

        switch ($command) {
            case 'HELO':
                $this->state = self::STATE_EHLO;
                $this->buffer = [$this->greeting];
                break;
            case 'EHLO':
                $this->state = self::STATE_EHLO;
                if ($this->isLegacy) {
                    $this->buffer = ['502 Command not implemented'];
                } else {
                    $this->buffer = \array_merge([$this->greeting], $this->advertisements);
                }
                break;
            case 'STARTTLS':
                $this->buffer = ['220 OK'];
                break;
            case 'AUTH':
                if (isset($parameters[1]) && \in_array($parameters[1], ['LOGIN', 'PLAIN'])) {
                    $this->buffer = ['330 Send credentials'];
                } else {
                    $this->buffer = ['400 Not supported'];
                }

                $this->state = self::STATE_AUTHORIZING;
                $this->metaData['auth']['method'] = $parameters[1];
                break;
            case 'MAIL':
                $this->buffer = ['220 OK'];
                $this->from = \substr($parameters[1], 5, -1);
                break;
            case 'RCPT':
                if ($this->from === '') {
                    throw new \RuntimeException('Invalid command at this state');
                }

                $this->to[] = \substr($parameters[1], 3, -1);
                $this->buffer = ['220 OK'];
                break;
            case 'DATA':
                if ($this->from === '' || empty($this->to)) {
                    throw new \RuntimeException('Invalid command at this state');
                }
                $this->buffer = ['334 Ready to receive data'];
                $this->state = self::STATE_DATA_RECEIVING;
                break;
            case 'QUIT':
                $this->buffer = ['220 OK'];
                break;
            case 'NOOP':
                $this->buffer = ['220 OK'];
                break;
            case '.':
                $this->buffer = [\sprintf('220 Sent from %s to %s recipients', $this->from, \count($this->to))];
                $this->from = '';
                $this->to = [];
                break;
            default:
                switch ($this->state) {
                    case self::STATE_AUTHORIZING:
                        $this->authenticate($request);
                        break;
                    case self::STATE_DATA_RECEIVING:
                        $this->buffer = ['220 OK'];
                        $this->state = self::STATE_DATA_RECEIVED;
                        break;
                    default:
                        throw new \RuntimeException('Invalid command at this state');
                }

                break;
        }

        return \strlen($request);
    }

    /**
     * @return string
     */
    public function receive(): string
    {
        if ($this->state === self::STATE_NONE) {
            throw new \RuntimeException('Cannot communicate while not connected');
        }

        $message = \array_shift($this->buffer);
        return $message . "\r\n";
    }

    /**
     * @param int $type
     */
    public function upgrade(int $type): void
    {
        if ($this->secure === true) {
            throw new \RuntimeException('Connection already secure');
        }

        $this->secure = true;
        $this->metaData['crypto'] = true;
    }

    /**
     * @param float $timeout
     */
    public function timeout(float $timeout): void
    {
        ;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMetaData(array $keys = []): array
    {
        $metaData = \array_merge(
            $this->metaData,
            [
                'state' => $this->state,
                'from' => $this->from,
                'to' => $this->to,
            ]
        );

        if (empty($keys)) {
            return $metaData;
        }

        $keys = \array_map('strtolower', $keys);

        return \array_filter(
            $metaData,
            function ($key) use ($keys) {
                return \in_array(\strtolower($key), $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param string $request
     */
    private function authenticate(string $request): void
    {
        if ($this->metaData['auth']['method'] === 'PLAIN') {
            $this->buffer = ['220 OK'];
            $this->metaData['auth']['state'] = true;
            $this->state = self::STATE_AUTHORIZED;

            [$null, $username, $password] = \explode("\0", \base64_decode($request));
            $this->metaData['auth']['username'] = $username;
            $this->metaData['auth']['password'] = $password;
        } else {
            if (isset($this->metaData['auth']['username'])) {
                $this->metaData['auth']['password'] = \base64_decode($request);
                $this->metaData['auth']['state'] = true;
                $this->buffer = ['220 OK'];
                $this->state = self::STATE_AUTHORIZED;
            } else {
                $this->metaData['auth']['username'] = \base64_decode($request);
                $this->buffer = ['330 Send password'];
                $this->state = self::STATE_AUTHORIZING;
            }
        }
    }
    
    private function reset()
    {
        $this->buffer = [];
        $this->state = self::STATE_NONE;
        $this->metaData = ['auth' => ['state' => false]];
    }
}
