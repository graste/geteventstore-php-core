<?php

namespace DB\EventStoreClient\Tests\Model;

use DB\EventStoreClient\Model\EventReference;
use DB\EventStoreClient\Model\StreamReference;

class EventReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersReturnProperValue()
    {
        $streamName = 'streamname';
        $streamVersion = 10;

        $reference = new EventReference(new StreamReference($streamName), $streamVersion);

        $this->assertSame($streamName, $reference->getStreamReference()->getStreamName());
        $this->assertSame($streamVersion, $reference->getStreamVersion());
    }
}
