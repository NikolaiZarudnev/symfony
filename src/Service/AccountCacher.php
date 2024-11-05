<?php

namespace App\Service;

use App\Entity\Account;
use Predis\ClientInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class AccountCacher
{
    private ClientInterface $client;

    private RedisAdapter $cache;

    public const ACCOUNT = 'account';

    public function __construct()
    {
        $this->client = RedisAdapter::createConnection($_ENV['REDIS_PROVIDER']);
        $this->cache = new RedisAdapter($this->client);
    }

    public function getAccount(): Account|null
    {
        $cacheItem = $this->cache->getItem(self::ACCOUNT);

        return $cacheItem->get();
    }

    public function setAccount(Account $account): void
    {
        $cacheItem = $this->cache->getItem(self::ACCOUNT);

        $cacheItem->set($account);

        $this->cache->save($cacheItem);
    }
}