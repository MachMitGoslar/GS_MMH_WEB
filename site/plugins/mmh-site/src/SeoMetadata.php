<?php

namespace Mmh\Site;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\FileVersion;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Filesystem\Asset;
use Kirby\Toolkit\Str;

/**
 * Resolves every meta value a page needs for search engines and social
 * embeds in one place, so the head snippet, the sitemap, the JSON-LD
 * snippet and the panel preview all agree on the same values.
 */
class SeoMetadata
{
    /** Open Graph reference size — every network crops to this ratio. */
    public const IMAGE_WIDTH = 1200;
    public const IMAGE_HEIGHT = 630;

    /** Formats that can be resized; anything else falls back to the default. */
    public const RASTER_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /** Templates that render as an article rather than a plain page. */
    public const ARTICLE_TEMPLATES = ['note', 'project_step'];

    /** Utility pages that should never show up in search results. */
    public const NOINDEX_TEMPLATES = [
        'booking-request',
        'booking-requests',
        'error',
        'forms',
        'newsletter-modal',
        'newsletter-unsubscribe',
        'signage',
    ];

    /** Content fields searched for a description, in order of preference. */
    public const DESCRIPTION_FIELDS = [
        'seo_description',
        'description',
        'subheadline',
        'wellcometext',
        'intro',
        'text',
    ];

    protected Site $site;

    public function __construct(protected ?Page $page = null)
    {
        $this->site = App::instance()->site();
    }

    public function page(): ?Page
    {
        return $this->page;
    }

    public function siteTitle(): string
    {
        return $this->site->title()->or('MachMit!Haus')->value();
    }

    /**
     * The page's own title, without the site name appended.
     */
    public function pageTitle(): string
    {
        if ($this->page === null) {
            return '';
        }

        foreach (['seo_title', 'headline'] as $fieldName) {
            $field = $this->page->content()->get($fieldName);

            if ($field->isNotEmpty() === true) {
                return trim((string) $field->value());
            }
        }

        return trim((string) $this->page->title()->value());
    }

    /**
     * Page title plus site name — what goes into <title> and og:title.
     */
    public function title(): string
    {
        $title = $this->pageTitle();

        return $title !== ''
            ? $title . ' | ' . $this->siteTitle()
            : $this->siteTitle();
    }

    public function description(): string
    {
        $description = $this->rawDescription();

        if ($description === '') {
            $description = $this->siteDescription();
        }

        return Str::excerpt($description, 160, false);
    }

    protected function rawDescription(): string
    {
        if ($this->page === null) {
            return '';
        }

        foreach (self::DESCRIPTION_FIELDS as $fieldName) {
            $value = trim(strip_tags((string) $this->page->content()->get($fieldName)->kt()));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function siteDescription(): string
    {
        $field = $this->site->content()->get('seo_description');

        if ($field->isNotEmpty() === true) {
            return trim(strip_tags((string) $field->kt()));
        }

        $home = $this->site->homePage();
        $welcome = $home
            ? trim(strip_tags((string) $home->content()->get('wellcometext')->kt()))
            : '';

        return $welcome !== '' ? $welcome : 'MachMit!Haus Goslar';
    }

    public function canonical(): string
    {
        return $this->page?->url() ?? $this->site->url();
    }

    /**
     * The source image for the social card, before any resizing.
     *
     * Order: explicit social image → the page's cover → the site-wide
     * default → the first image on the page.
     */
    public function sourceImage(): ?File
    {
        if ($this->page !== null) {
            if ($file = $this->fieldFile($this->page, 'social_image')) {
                return $file;
            }

            if ($file = $this->pageCover()) {
                return $file;
            }
        }

        if ($file = $this->fieldFile($this->site, 'social_image')) {
            return $file;
        }

        return $this->page?->image();
    }

    /**
     * The page cover, which some models already resolve to a File while
     * others still hand back the raw content field.
     */
    protected function pageCover(): ?File
    {
        if ($this->page === null) {
            return null;
        }

        if (method_exists($this->page, 'cover') === true) {
            $cover = $this->page->cover();

            if ($cover instanceof File) {
                return $cover;
            }

            if (is_object($cover) === true && method_exists($cover, 'toFile') === true) {
                if ($file = $cover->toFile()) {
                    return $file;
                }
            }
        }

        return $this->fieldFile($this->page, 'cover');
    }

    protected function fieldFile(Page|Site $model, string $fieldName): ?File
    {
        $field = $model->content()->get($fieldName);

        return $field->isNotEmpty() === true ? $field->toFile() : null;
    }

    /**
     * The social card image, cropped to the Open Graph reference size.
     * Returns null when there is no usable raster source.
     */
    public function image(): File|FileVersion|Asset|null
    {
        $image = $this->sourceImage();

        if ($image === null) {
            return null;
        }

        if (in_array(strtolower($image->extension()), self::RASTER_EXTENSIONS, true) === false) {
            return null;
        }

        return $image->thumb([
            'width' => self::IMAGE_WIDTH,
            'height' => self::IMAGE_HEIGHT,
            'crop' => true,
            'quality' => 85,
        ]);
    }

    public function imageUrl(): string
    {
        return $this->image()?->url() ?? $this->defaultImageUrl();
    }

    /**
     * The branded fallback card. Lives in the site's asset folder, so the
     * path is an option rather than a constant.
     */
    public function defaultImageUrl(): string
    {
        $path = App::instance()->option(
            'gs-mmh.seo.defaultImage',
            'assets/pngs/og-default.png',
        );

        return $this->site->url() . '/' . ltrim($path, '/');
    }

    public function imageType(): string
    {
        return $this->image()?->mime() ?? 'image/png';
    }

    public function imageAlt(): string
    {
        $alt = trim((string) $this->sourceImage()?->alt()?->value());

        return $alt !== '' ? $alt : $this->pageTitle();
    }

    public function type(): string
    {
        return in_array($this->page?->intendedTemplate()->name(), self::ARTICLE_TEMPLATES, true)
            ? 'article'
            : 'website';
    }

    public function isArticle(): bool
    {
        return $this->type() === 'article';
    }

    /**
     * Whether search engines may index this page. The panel field wins;
     * without it, unpublished pages and known utility templates stay out.
     */
    public function isIndexable(): bool
    {
        if ($this->page === null) {
            return true;
        }

        // A page below an excluded parent stays excluded, whatever its
        // own template says — otherwise signage screens and form pages
        // leak back into the sitemap.
        foreach ($this->page->parents() as $parent) {
            if ($this->isPageIndexable($parent) === false) {
                return false;
            }
        }

        return $this->isPageIndexable($this->page);
    }

    protected function isPageIndexable(Page $page): bool
    {
        $field = $page->content()->get('robots');

        if ($field->isNotEmpty() === true) {
            return $field->value() !== 'noindex';
        }

        // Unlisted is a menu decision, not a search decision — home,
        // contact and imprint all live outside the listing.
        if ($page->isDraft() === true) {
            return false;
        }

        return in_array($page->intendedTemplate()->name(), self::NOINDEX_TEMPLATES, true) === false;
    }

    public function robots(): string
    {
        return $this->isIndexable() ? 'index,follow' : 'noindex,follow';
    }

    public function publishedTime(): ?string
    {
        $field = $this->page?->content()->get('date');

        return $field?->isNotEmpty() === true
            ? $field->toDate('c')
            : null;
    }

    public function modifiedTime(): ?string
    {
        return $this->page?->modified('c') ?: null;
    }

    public function locale(): string
    {
        return 'de_DE';
    }

    public function twitterCard(): string
    {
        return 'summary_large_image';
    }

    public function twitterSite(): ?string
    {
        $handle = trim((string) $this->site->content()->get('twitter_handle')->value());

        if ($handle === '') {
            return null;
        }

        return Str::startsWith($handle, '@') ? $handle : '@' . $handle;
    }

    /**
     * Everything at once — used by the panel preview and the JSON-LD snippet.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title(),
            'pageTitle' => $this->pageTitle(),
            'siteTitle' => $this->siteTitle(),
            'description' => $this->description(),
            'canonical' => $this->canonical(),
            'imageUrl' => $this->imageUrl(),
            'imageType' => $this->imageType(),
            'imageAlt' => $this->imageAlt(),
            'imageWidth' => self::IMAGE_WIDTH,
            'imageHeight' => self::IMAGE_HEIGHT,
            'type' => $this->type(),
            'robots' => $this->robots(),
            'indexable' => $this->isIndexable(),
            'publishedTime' => $this->publishedTime(),
            'modifiedTime' => $this->modifiedTime(),
            'locale' => $this->locale(),
            'twitterCard' => $this->twitterCard(),
            'twitterSite' => $this->twitterSite(),
        ];
    }
}
