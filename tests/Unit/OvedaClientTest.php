<?php

use Mmh\Site\Oveda\Client;
use PHPUnit\Framework\TestCase;

final class OvedaClientTest extends TestCase
{
    public function testTransportErrorsBecomeAnErrorPageNotAnException(): void
    {
        // Nothing listens on port 1: Remote::get() throws, the client must catch it.
        $page = Client::fetchEventDatePage('http://127.0.0.1:1/', 2, 12);

        $this->assertSame([], $page['items']);
        $this->assertFalse($page['has_next']);
        $this->assertNotNull($page['error']);
        $this->assertSame(2, $page['page']);
        $this->assertSame(12, $page['per_page']);
    }

    public function testGetReturnsNullInsteadOfThrowing(): void
    {
        $this->assertNull(Client::get('http://127.0.0.1:1/'));
    }

    public function testHelpers(): void
    {
        $this->assertSame('https://oveda.de/media/x.jpg', Client::absoluteUrl('/media/x.jpg'));
        $this->assertSame('https://example.org/y.jpg', Client::absoluteUrl('https://example.org/y.jpg'));
        $this->assertNull(Client::absoluteUrl('  '));
        $this->assertSame('abc', Client::cleanString('  abc '));
        $this->assertSame('', Client::cleanString(null));
        $this->assertSame('Europe/Berlin', Client::timezone()->getName());
    }
}
