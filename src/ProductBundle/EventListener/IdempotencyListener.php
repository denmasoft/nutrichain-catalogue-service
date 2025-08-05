<?php

namespace ProductBundle\EventListener;

use Predis\Client as RedisClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IdempotencyListener implements EventSubscriberInterface
{
    private $redis;

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 250],
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) return;

        $request = $event->getRequest();
        $idempotencyKey = $request->headers->get('Idempotency-Key');

        if (!$idempotencyKey || !in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        $responseKey = 'idempotency_response:' . $idempotencyKey;
        $lockKey = 'idempotency_lock:' . $idempotencyKey;

        if ($cachedResponse = $this->redis->get($responseKey)) {
            $data = json_decode($cachedResponse, true);
            $response = new Response($data['content'], $data['status'], $data['headers']);
            $event->setResponse($response);
            $event->stopPropagation();
            return;
        }

        if ($this->redis->get($lockKey)) {
            throw new HttpException(409, 'A request with this idempotency key is already being processed.');
        }

        $this->redis->set($lockKey, 1, 'EX', 30);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) return;

        $request = $event->getRequest();
        $response = $event->getResponse();
        $idempotencyKey = $request->headers->get('Idempotency-Key');

        if (!$idempotencyKey || !$response->isSuccessful()) {
            return;
        }

        $responseKey = 'idempotency_response:' . $idempotencyKey;
        $lockKey = 'idempotency_lock:' . $idempotencyKey;

        $dataToCache = json_encode([
            'content' => $response->getContent(),
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all()
        ]);

        $this->redis->transaction(function ($tx) use ($responseKey, $lockKey, $dataToCache) {
            $tx->set($responseKey, $dataToCache, 'EX', 86400); // 24 horas
            $tx->del($lockKey);
        });
    }
}
