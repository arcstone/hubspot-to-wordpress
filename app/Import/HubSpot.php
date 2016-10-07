<?php

namespace H2W\Import;

use H2W\API\HubSpot as HubSpotAPI;

class HubSpot
{
    /**
     * @var HubSpotAPI
     */
    private $hubSpot;

    public function __construct(HubSpotAPI $hubSpot)
    {
        $this->hubSpot = $hubSpot;
    }
}
