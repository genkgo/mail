<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Protocol\AutomaticConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\Negotiation\TryTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\Mail\Protocol\PlainTcpConnection;
use Genkgo\Mail\Protocol\SecureConnection;
use Genkgo\Mail\Protocol\SecureConnectionOptions;

final class ClientFactory
{
    private const AUTH_ENUM = [
        Client::AUTH_NONE => true,
        Client::AUTH_PLAIN => true,
        Client::AUTH_LOGIN => true,
        Client::AUTH_AUTO => true
    ];

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var float
     */
    private $timeout = 1;

    /**
     * @var string
     */
    private $username = '';

    /**
     * @var int
     */
    private $authMethod = Client::AUTH_NONE;

    /**
     * @var bool
     */
    private $insecureConnectionAllowed = false;

    /**
     * @var string
     */
    private $reconnectAfter = 'PT300S';

    /**
     * @var int
     */
    private $startTls;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->startTls = CryptoConstant::getDefaultMethod(PHP_VERSION);
    }

    /**
     * @param float $connectionTimeout
     * @return ClientFactory
     */
    public function withTimeout(float $connectionTimeout): ClientFactory
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @param int $method
     * @param string $password
     * @param string $username
     * @return ClientFactory
     */
    public function withAuthentication(int $method, string $username, string $password): ClientFactory
    {
        if (!isset(self::AUTH_ENUM[$method])) {
            throw new \InvalidArgumentException('Invalid authentication method');
        }

        $clone = clone $this;
        $clone->authMethod = $method;
        $clone->username = $username;
        $clone->password = $password;
        return $clone;
    }

    /**
     * @return ClientFactory
     */
    public function withInsecureConnectionAllowed(): ClientFactory
    {
        $clone = clone $this;
        $clone->insecureConnectionAllowed = true;
        return $clone;
    }

    /**
     * @param int $crypto
     * @return ClientFactory
     */
    public function withStartTls(int $crypto): ClientFactory
    {
        $clone = clone $this;
        $clone->startTls = $crypto;
        return $clone;
    }

    /**
     * @return ClientFactory
     */
    public function withoutStartTls(): ClientFactory
    {
        $clone = clone $this;
        $clone->startTls = 0;
        return $clone;
    }

    /**
     * @return Client
     */
    public function newClient(): Client
    {
        $negotiators = [];

        if ($this->startTls !== 0) {
            if ($this->insecureConnectionAllowed) {
                $negotiators[] = new TryTlsUpgradeNegotiation(
                    $this->connection,
                    $this->startTls
                );
            } else {
                $negotiators[] = new ForceTlsUpgradeNegotiation(
                    $this->connection,
                    $this->startTls
                );
            }
        }

        if ($this->authMethod !== Client::AUTH_NONE) {
            $negotiators[] = new AuthNegotiation(
                $this->authMethod,
                $this->username,
                $this->password
            );
        }

        return new Client(
            new AutomaticConnection(
                $this->connection,
                new \DateInterval($this->reconnectAfter)
            ),
            new GeneratorTagFactory(),
            $negotiators
        );
    }

    /**
     * @param string $dataSourceName
     * @return ClientFactory
     */
    public static function fromString(string $dataSourceName):ClientFactory
    {
        $components = \parse_url($dataSourceName);
        if (!isset($components['scheme']) || !isset($components['host'])) {
            throw new \InvalidArgumentException('Scheme and host are required');
        }

        if (isset($components['query'])) {
            \parse_str($components['query'], $query);
        } else {
            $query = [];
        }

        $insecureConnectionAllowed = false;
        switch ($components['scheme']) {
            case 'imap':
                $connection = new PlainTcpConnection(
                    $components['host'],
                    $components['port'] ?? 143
                );
                break;
            case 'imaps':
                $connection = new SecureConnection(
                    $components['host'],
                    $components['port'] ?? 993,
                    new SecureConnectionOptions(
                        (int)($query['crypto'] ?? CryptoConstant::getDefaultMethod(PHP_VERSION))
                    )
                );
                break;
            case 'imap-starttls':
                $insecureConnectionAllowed = true;
                $connection = new PlainTcpConnection(
                    $components['host'],
                    $components['port'] ?? 143
                );
                break;
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Provided scheme "%s://" is invalid. Only imap:// imaps:// and imap-starttls:// are supported',
                    $components['scheme']
                ));
        }

        $factory = new self($connection);
        $factory->insecureConnectionAllowed = $insecureConnectionAllowed;

        if (isset($components['user']) && isset($components['pass'])) {
            $factory->authMethod = Client::AUTH_AUTO;
            $factory->username = \urldecode($components['user']);
            $factory->password = \urldecode($components['pass']);
        }

        if (isset($query['timeout'])) {
            $factory->timeout = (float)$query['timeout'];
        }

        if (isset($query['reconnectAfter'])) {
            $factory->reconnectAfter = $query['reconnectAfter'];
        }

        if (isset($query['crypto'])) {
            $factory->startTls = (int)$query['crypto'];
        }

        return $factory;
    }
}
