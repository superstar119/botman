<?php

namespace Mpociot\BotMan\Tests\Drivers;

use Mockery as m;
use Mpociot\BotMan\Http\Curl;
use PHPUnit_Framework_TestCase;
use Mpociot\BotMan\Drivers\HipChatDriver;
use Symfony\Component\HttpFoundation\Request;

class HipChatDriverTest extends PHPUnit_Framework_TestCase
{
    private function getDriver($responseData, $htmlInterface = null)
    {
        $request = m::mock(Request::class.'[getContent]');
        $request->shouldReceive('getContent')->andReturn(json_encode($responseData));
        if ($htmlInterface === null) {
            $htmlInterface = m::mock(Curl::class);
        }

        return new HipChatDriver($request, [], $htmlInterface);
    }

    /** @test */
    public function it_matches_the_request()
    {
        $driver = $this->getDriver([
            'to' => '41766013098',
            'messageId' => '0C000000075069C7',
            'text' => 'Hi Julia',
            'type' => 'text',
            'keyword' => 'HEY',
            'message_timestamp' => '2016-11-30 19:27:46',
        ]);
        $this->assertFalse($driver->matchesRequest());

        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertTrue($driver->matchesRequest());
    }

    /** @test */
    public function it_returns_the_message_object()
    {
        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertTrue(is_array($driver->getMessages()));
    }

    /** @test */
    public function it_returns_the_message_text()
    {
        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertSame('Hi Julia', $driver->getMessages()[0]->getMessage());
    }

    /** @test */
    public function it_detects_bots()
    {
        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertFalse($driver->isBot());
    }

    /** @test */
    public function it_returns_the_user_id()
    {
        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertSame('12345', $driver->getMessages()[0]->getUser());
    }

    /** @test */
    public function it_returns_the_channel_id()
    {
        $driver = $this->getDriver([
            'event' => 'room_message',
            'item' => [
                'message' => [
                    'from' => [
                        'id' => '12345',
                    ],
                    'message' => 'Hi Julia',
                ],
                'room' => [
                    'id' => '98765',
                ],
            ],
            'webhook_id' => '11223344',
        ]);
        $this->assertSame('98765', $driver->getMessages()[0]->getChannel());
    }
}
