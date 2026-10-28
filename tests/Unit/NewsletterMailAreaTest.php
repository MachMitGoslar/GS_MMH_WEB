<?php

use PHPUnit\Framework\TestCase;

/**
 * The panel area to send a newsletter lives in mmh-site (it renders the
 * website's newsletter page); recipients come from gs-mmh-web-plugin.
 */
final class NewsletterMailAreaTest extends TestCase
{
    private static function area(): array
    {
        $area = require dirname(__DIR__, 2) . '/site/plugins/mmh-site/areas/newsletter-mail.php';

        return $area(kirby());
    }

    public function testTheAreaIsRegisteredByMmhSite(): void
    {
        $extensions = require dirname(__DIR__, 2) . '/site/plugins/mmh-site/extensions.php';

        $this->assertArrayHasKey('newsletter-mail', $extensions['areas']);
        $this->assertIsCallable($extensions['areas']['newsletter-mail']);
    }

    public function testTheAreaOffersSendingToOneAndToAll(): void
    {
        $area = self::area();

        $this->assertSame('Newsletter-Mail', $area['label']);
        $this->assertFalse($area['menu']);
        $this->assertArrayHasKey('page.sendNewsletter', $area['buttons']);
        $this->assertContains('newsletter-mail/send/(:all)', array_keys($area['dialogs']));
        $this->assertContains('newsletter-mail/send-all/(:all)', array_keys($area['dialogs']));
    }

    public function testTheWebPluginNoLongerNeedsSiteFunctions(): void
    {
        $classes = glob(dirname(__DIR__, 2) . '/site/plugins/gs-mmh-web-plugin/{classes,areas,routes}/*.php', GLOB_BRACE);

        $this->assertNotEmpty($classes);

        foreach ($classes as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bmmh(?:AbsoluteUrl|NewsletterMobileHtml|Version|BackLink|ColorContrast)\(/',
                (string) file_get_contents($file),
                basename($file) . ' calls a function of the website',
            );
        }
    }
}
