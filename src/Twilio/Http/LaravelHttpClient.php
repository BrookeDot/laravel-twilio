<?php

namespace BabDev\Twilio\Twilio\Http;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Twilio\Exceptions\HttpException;
use Twilio\Http\Client;
use Twilio\Http\Response;

final class LaravelHttpClient implements Client
{
    /**
     * @var Factory
     */
    private $httpFactory;

    /**
     * Instantiate the HTTP client
     *
     * @param Factory $httpFactory
     */
    public function __construct(Factory $httpFactory)
    {
        $this->httpFactory = $httpFactory;
    }

    /**
     * @param string      $method   The request method to use
     * @param string      $url      The URI to send the request to
     * @param array       $params   Query parameters for the request
     * @param array       $data     The request body
     * @param array       $headers  Request headers
     * @param string|null $user     The username to authenticate with
     * @param string|null $password The password to authenticate with
     * @param int|null    $timeout  The request timeout
     *
     * @return Response
     *
     * @throws HttpException if the request cannot be completed
     */
    public function request(
        string $method,
        string $url,
        array $params = [],
        array $data = [],
        array $headers = [],
        string $user = null,
        string $password = null,
        int $timeout = null
    ): Response {
        /** @var PendingRequest $request */
        $request = $this->httpFactory->asForm();

        if ($user && $password) {
            $request->withBasicAuth($user, $password);
        }

        $request->withHeaders($headers);

        $requestOptions = [
            'form_params' => $data,
            'query' => $params,
        ];

        try {
            $response = $request->send(
                $method,
                $url,
                $requestOptions
            );
        } catch (\Exception $exception) {
            throw new HttpException('Unable to complete the HTTP request', 0, $exception);
        }

        return new Response($response->status(), $response->body(), $response->headers());
    }
}
