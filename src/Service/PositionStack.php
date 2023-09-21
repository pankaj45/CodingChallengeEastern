<?php

namespace App\Service;


use App\DTO\PositionStackResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PositionStack
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly string              $apiUrl,
        private readonly string              $apiKey,
    ) {
    }

    public function fetchAddress(string $address): ?PositionStackResponse
    {
        $apiResponse = null;
        try {
            $response = $this->client->request(
                'GET',
                "{$this->apiUrl}/forward",
                [
                    'query' => [
                        'access_key' => $this->apiKey,
                        'query' => $address,
                        'limit' => 1
                    ],
                ]
            );

            $content = $response->getContent();
            /** @var PositionStackResponse $apiResponse */
            $apiResponse = $this->serializer->deserialize($content, PositionStackResponse::class, 'json');
        } catch (\Exception $exception) {
           $this->logger->error("Error in address API: {$address} error_message: {$exception->getMessage()}");
        }

        return $apiResponse;
    }
}
