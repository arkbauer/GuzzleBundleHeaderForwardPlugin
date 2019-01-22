<?php

namespace EncoreLabs\Bundle\GuzzleBundleHeaderForwardPlugin;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GuzzleForwardHeaderMiddleware
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * GuzzleForwardHeaderMiddleware constructor.
     *
     * @param RequestStack $requestStack
     * @param string[]     $headers
     */
    public function __construct(RequestStack $requestStack, array $headers = [])
    {
        $this->requestStack = $requestStack;
        $this->headers      = $headers;
    }

    /**
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function(RequestInterface $request, array $options) use (&$handler) {
            if (empty($this->headers)) {
                return $handler;
            }

            $currentRequest = $this->requestStack->getCurrentRequest();

            foreach ($this->headers as $header) {
                if ($currentRequest->headers->has($header)) {
                    $request = $request->withHeader($header, $currentRequest->headers->get($header));
                }
            }

            return $handler($request, $options);
        };
    }
}
