<?php

namespace ProductBundle\EventListener;

use Predis\Client as RedisClient;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RateLimiterListener
{
    private $redis;
    private $translator;
    private $limit;
    private $period;
    private $tokenStorage;
    private $authenticatedLimit;
    private $anonymousLimit;

    public function __construct(
        RedisClient $redis,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        int $authenticatedLimit,
        int $anonymousLimit,
        int $period
    ) {
        $this->redis = $redis;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->authenticatedLimit = $authenticatedLimit;
        $this->anonymousLimit = $anonymousLimit;
        $this->period = $period;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $limit = $this->anonymousLimit;

        $token = $this->tokenStorage->getToken();

        if ($token && $token->isAuthenticated() && $token->getUsername() !== 'anon.') {
            $identifier = $token->getUsername();
            $limit = $this->authenticatedLimit;
        } else {
            $identifier = $request->getClientIp();
        }

        $key = sprintf('rate_limit:%s', $identifier);

        $responses = $this->redis->transaction(function ($tx) use ($key) {
            $tx->incr($key);
            $tx->expire($key, $this->period);
        });

        $currentRequests = $responses[0];

        if ($currentRequests > $limit) {
            $message = $this->translator->trans('error.rate_limit_exceeded');
            $headers = [
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $this->period
            ];
            throw new HttpException(429, $message, null, $headers);
        }
    }
}
