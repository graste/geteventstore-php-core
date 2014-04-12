<?php

namespace EventStore\Tests;

use EventStore\Connection;
use EventStore\ConnectionInterface;
use EventStore\EventData;
use EventStore\Tests\Guzzle\GuzzleTestCase;
use GuzzleHttp\Message\Response;

class ConnectionTest extends GuzzleTestCase
{
    public function testHardDeleteStreamWorksProperly()
    {
        $this->streamDeleteCommon(true);
        $this->assertEquals('true', $this->request->getHeader('ES-HardDelete'));
    }

    public function testSoftDeleteStreamHasNotHardDeleteHeader()
    {
        $this->streamDeleteCommon(false);
        $this->assertFalse($this->request->hasHeader('ES-HardDelete'), 'ES-HardDelete header should not be present');
    }

    private function streamDeleteCommon($hardDelete)
    {
        $guzzle = $this->buildMockClient(function () {
            return new Response(204);
        });

        $connection = new Connection($guzzle);
        $connection->deleteStream('example', $hardDelete);

        $this->assertRequestPresent();
        $this->assertEquals('DELETE', $this->request->getMethod());
        $this->assertEquals('/streams/example', $this->request->getResource());

        $this->assertEquals('application/json', $this->request->getHeader('Content-type'));
    }

    public function testAppendEventMakesCorrectHttpRequest()
    {
        $guzzle = $this->buildMockClient(function () {
            return new Response(201);
        });

        $connection = new Connection($guzzle);

        $eventId = 'df0582d9-b0c5-4898-93d7-f027b71424b6';
        $type = 'TestEvent';
        $data = ['foo' => 'bar'];

        $event = new EventData($eventId, $type, $data);
        $connection->appendToStream('example', ConnectionInterface::STREAM_VERSION_ANY, [$event]);

        $this->assertRequestPresent();

        $expectedBody = json_encode([[
            'eventId' => $event->getEventId(),
            'eventType' => $event->getType(),
            'data' => $data
        ]]);

        $this->assertEquals(ConnectionInterface::STREAM_VERSION_ANY, $this->request->getHeader('ES-ExpectedVersion'));
        $this->assertJsonStringEqualsJsonString($expectedBody, (string) $this->request->getBody());
        $this->assertEquals('application/json', $this->request->getHeader('Content-type'));
    }
}