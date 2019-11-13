<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../utils/arraySerializer.php';

class ExternalId implements ArraySerializer
{
    use ArraySerializerImpl;

    const MAX_EID_LENGTH = 255;

    private $clickId;
    private $externalId;

    /**
     * @param $clickId string
     * @param $externalId string
     */
    function __construct($clickId, $externalId)
    {
        $this->clickId = $clickId;
        $this->externalId = $externalId;
    }
}
