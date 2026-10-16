<?php

use PHPUnit\Framework\TestCase;

final class MemberPageTest extends TestCase
{
    private static function member(array $content = []): MemberPage
    {
        return new MemberPage(['slug' => 'person', 'content' => $content]);
    }

    public function testSocialLinksBuildProfileUrlsFromHandles(): void
    {
        $links = self::member(['instagram' => '@machmitgoslar', 'github' => 'rangarius'])->socialLinks();

        $this->assertSame(
            [
                ['label' => 'Instagram', 'icon' => 'instagram', 'value' => '@machmitgoslar', 'href' => 'https://instagram.com/machmitgoslar'],
                ['label' => 'GitHub', 'icon' => 'github', 'value' => '@rangarius', 'href' => 'https://github.com/rangarius'],
            ],
            $links
        );
    }

    public function testPastedProfileUrlsAreKept(): void
    {
        $links = self::member(['linkedin' => 'https://www.linkedin.com/in/jemand/'])->socialLinks();

        $this->assertSame('https://www.linkedin.com/in/jemand/', $links[0]['href']);
        $this->assertSame('in/jemand', $links[0]['value']);
    }

    public function testNoProfilesMeansNoSocialMedia(): void
    {
        $member = self::member(['email' => 'a@b.de']);

        $this->assertSame([], $member->socialLinks());
        $this->assertFalse($member->hasSocialMedia());
        $this->assertTrue(self::member(['x' => 'goslar'])->hasSocialMedia());
    }
}
