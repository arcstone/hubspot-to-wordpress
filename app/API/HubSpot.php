<?php

namespace H2W\API;

use GuzzleHttp\ClientInterface;

class HubSpot
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function getBlogs()
    {
        try {
            return json_decode((string) $this->guzzle->request('GET', 'blogs'/*, ['debug' => true]*/)
                ->getBody()
                ->getContents(),
                true);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
