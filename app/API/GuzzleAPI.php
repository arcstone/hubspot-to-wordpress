<?php

namespace H2W\API;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;

trait GuzzleAPI
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $guzzle;
    /**
     * @var bool
     */
    protected $verbose = false;

    protected function request($route, $query = [], $method = 'GET', $cache = true)
    {
        try {
            if ($method === 'GET') {
                $requestFunction = function () use ($route, $query, $method) {
                    $data = json_decode((string) $this->guzzle->request($method, $route, [
                        'query' => array_merge($this->guzzle->getConfig('query') ?? [], $query),
                        'debug' => $this->verbose,
                    ])
                        ->getBody()
                        ->getContents(),
                        true);

                    if ($data === null) {
                        throw new \Exception(static::class . ': Invalid data received from API: ' .
                                             json_last_error_msg());
                    }

                    return $data;
                };

                if ($cache === true) {
                    return \Cache::remember($method . $route . http_build_query($query), 60, $requestFunction);
                } else {
                    return $requestFunction();
                }
            }

            $response = $this->guzzle->request($method, $route, [
                'json'  => $query,
                'debug' => $this->verbose,
            ]);

            $data = json_decode((string) $response->getBody()->getContents(), true);

            if ($data === null) {
                throw new \Exception(static::class . ': Invalid data received from API: ' . json_last_error_msg());
            }

            return $data;
        } catch (RequestException $e) {
            $response          = json_decode($e->getResponse()->getBody(), true);
            $response['query'] = $query;
            event('console.error', [
                "Unable to complete $method request: $route. Reason: {$response['message']}",
                $response,
            ]);

            throw $e;
        }
    }

    /**
     * @param string $route
     * @param string $model
     * @param array  $query
     * @param string $totalField
     * @return Collection
     */
    protected function getCollection($route, $model, $query = [], $totalField = 'total_count')
    {
        $perPage = 10;
        $posts   = new Collection;
        $offset  = $query['offset'] ?? 0;

        do {
            $response = $this->request($route, array_merge($query, [
                'limit'  => $perPage,
                'offset' => $offset,
            ]));

            foreach ($response['objects'] as $object) {
                $posts->push(new $model($object));
            }

            $offset += $perPage;
        } while ($response[$totalField] > $offset);

        return $posts;
    }

    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }
}
