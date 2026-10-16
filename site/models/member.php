<?php

use Kirby\Cms\Page;

class MemberPage extends Page
{
    public function cover()
    {
        if ($this->content()->cover() && $this->content()->cover()->exists()) {
            return $this->content()->cover()->toFile();
        } else {
            return $this->image();
        }
    }

    public function hasAnyContactInfo()
    {
        return $this->email()->isNotEmpty() || $this->phone()->isNotEmpty();
    }

    public function hasSocialMedia()
    {
        return $this->socialLinks() !== [];
    }

    /**
     * The profiles of this person: label, icon, shown handle and link. Fields hold
     * the handle (a leading @ and a pasted profile URL are tolerated).
     *
     * @return array<int, array{label: string, icon: string, value: string, href: string}>
     */
    public function socialLinks(): array
    {
        // field => [label, icon, profile base URL, shown with @]
        $networks = [
            'instagram' => ['Instagram', 'instagram', 'https://instagram.com/', true],
            'facebook' => ['Facebook', 'facebook', 'https://facebook.com/', false],
            'linkedin' => ['LinkedIn', 'linkedin', 'https://linkedin.com/in/', false],
            'youtube' => ['YouTube', 'youtube', 'https://youtube.com/', false],
            'github' => ['GitHub', 'github', 'https://github.com/', true],
            'x' => ['X', 'x', 'https://x.com/', true],
        ];
        $links = [];

        foreach ($networks as $field => [$label, $icon, $base, $withAt]) {
            $value = trim((string) $this->content()->get($field)->value());

            if ($value === '') {
                continue;
            }

            if (preg_match('#^https?://#i', $value)) {
                $href = $value;
                $handle = trim((string) parse_url($value, PHP_URL_PATH), '/');
            } else {
                $handle = ltrim($value, '@');
                $href = $base . rawurlencode($handle);
            }

            $handle = ltrim($handle, '@');
            $links[] = ['label' => $label, 'icon' => $icon, 'value' => $handle === '' ? $label : ($withAt ? '@' : '') . $handle, 'href' => $href];
        }

        return $links;
    }
}
