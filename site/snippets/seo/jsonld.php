<?php

/**
 * Structured data (schema.org) as a single @graph.
 *
 * Organisation and website are emitted on every page so that search
 * engines can attach the page node to them; the page node itself becomes
 * an Article on templates that render editorial content.
 *
 * @var \Kirby\Cms\Site $site
 * @var \Kirby\Cms\Page|null $page
 * @var \Mmh\Site\SeoMetadata $meta
 */

$organizationId = $site->url() . '#organization';
$websiteId = $site->url() . '#website';

$contact = $site->find('contact');

$organization = [
    '@type' => 'NGO',
    '@id' => $organizationId,
    'name' => $meta->siteTitle(),
    'url' => $site->url(),
    'logo' => $meta->defaultImageUrl(),
];

if ($contact) {
    if ($email = trim((string) $contact->content()->get('email')->value())) {
        $organization['email'] = $email;
    }

    if ($phone = trim((string) $contact->content()->get('phone')->value())) {
        $organization['telephone'] = $phone;
    }

    $address = trim((string) $contact->content()->get('address')->value());

    if ($address !== '') {
        // Stored as "Street 1, 12345 City"
        $parts = array_map('trim', explode(',', $address, 2));
        $postalAddress = [
            '@type' => 'PostalAddress',
            'streetAddress' => $parts[0],
            'addressCountry' => 'DE',
        ];

        if (isset($parts[1]) && preg_match('/^(\d{5})\s+(.+)$/', $parts[1], $matches) === 1) {
            $postalAddress['postalCode'] = $matches[1];
            $postalAddress['addressLocality'] = $matches[2];
        } elseif (isset($parts[1])) {
            $postalAddress['addressLocality'] = $parts[1];
        }

        $organization['address'] = $postalAddress;
    }
}

$sameAs = [];

foreach ($site->content()->get('social')->toStructure() as $entry) {
    if ($link = trim((string) $entry->link()->value())) {
        $sameAs[] = $link;
    }
}

if ($sameAs !== []) {
    $organization['sameAs'] = $sameAs;
}

$website = [
    '@type' => 'WebSite',
    '@id' => $websiteId,
    'url' => $site->url(),
    'name' => $meta->siteTitle(),
    'inLanguage' => 'de-DE',
    'publisher' => ['@id' => $organizationId],
];

$node = [
    '@type' => $meta->isArticle() ? 'Article' : 'WebPage',
    '@id' => $meta->canonical() . '#webpage',
    'url' => $meta->canonical(),
    'name' => $meta->title(),
    'headline' => $meta->pageTitle(),
    'description' => $meta->description(),
    'inLanguage' => 'de-DE',
    'isPartOf' => ['@id' => $websiteId],
    'primaryImageOfPage' => ['@id' => $meta->canonical() . '#primaryimage'],
];

if ($meta->isArticle()) {
    $node['image'] = ['@id' => $meta->canonical() . '#primaryimage'];
    $node['publisher'] = ['@id' => $organizationId];

    if ($published = $meta->publishedTime()) {
        $node['datePublished'] = $published;
    }

    if ($modified = $meta->modifiedTime()) {
        $node['dateModified'] = $modified;
    }
}

$primaryImage = [
    '@type' => 'ImageObject',
    '@id' => $meta->canonical() . '#primaryimage',
    'url' => $meta->imageUrl(),
    'contentUrl' => $meta->imageUrl(),
    'width' => $meta::IMAGE_WIDTH,
    'height' => $meta::IMAGE_HEIGHT,
];

if ($alt = $meta->imageAlt()) {
    $primaryImage['caption'] = $alt;
}

$graph = [
    '@context' => 'https://schema.org',
    '@graph' => [$organization, $website, $node, $primaryImage],
];
?>
<script type="application/ld+json"><?= json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
