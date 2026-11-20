<?php

declare(strict_types=1);

namespace Mmh\Site;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\VersionId;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;

/**
 * Central image library ("Mediathek").
 *
 * Images live in albums below the page `mediathek`; fields everywhere pick
 * them through `fields/mediathek-image` and reference them by UUID, so an
 * image can be moved between albums without breaking anything.
 *
 * This class guards the library:
 * - duplicates: an image upload that already exists in the library is
 *   rejected with a hint where to find it (hash of the original upload,
 *   stored as `source_hash`, because large uploads get resized on create)
 * - usage: which pages reference an image (`file://<uuid>` in their content)
 * - deletion: an image that is still in use cannot be deleted
 * - moving: Kirby cannot move files between pages; move() does it for
 *   library images (file + content, UUID stays, so references keep working)
 */
final class Mediathek
{
    public const ROOT = 'mediathek';
    public const INBOX = 'mediathek/eingang';
    public const FILE_TEMPLATE = 'mediathek-image';
    public const ALBUM_TEMPLATE = 'mediathek-album';

    /**
     * Parents whose uploads are never checked (form uploads of visitors).
     */
    private const IGNORED_ROOTS = ['forms'];

    /**
     * Hashes of uploads between the `before` and `after` create hook.
     *
     * @var array<string, string>
     */
    private static array $pendingHashes = [];

    /**
     * Usage index per request: file uuid => page ids.
     *
     * @var array<string, string[]>|null
     */
    private static ?array $usageIndex = null;

    public static function root(): ?Page
    {
        return \kirby()->page(self::ROOT);
    }

    /**
     * All images of the library.
     *
     * @return \Kirby\Cms\Files<File>
     */
    public static function images(): \Kirby\Cms\Files
    {
        $root = self::root();

        return $root
            ? $root->index(true)->files()->add($root->files())->filterBy('type', 'image')
            : new \Kirby\Cms\Files([]);
    }

    public static function isInLibrary(File $file): bool
    {
        $parent = $file->parent();

        return $parent instanceof Page
            && ($parent->id() === self::ROOT || str_starts_with($parent->id(), self::ROOT . '/'));
    }

    /**
     * Hash of the original image: the stored `source_hash`, else the file
     * itself (images that were never resized).
     */
    public static function hashOf(File $file): ?string
    {
        $stored = trim((string) $file->content()->get('source_hash')->value());
        if ($stored !== '') {
            return $stored;
        }

        return self::fileHash($file);
    }

    /**
     * Every hash under which an image counts as known: the original upload
     * and the stored file (Kirby re-encodes images on create, so someone
     * downloading and re-uploading a library image brings the stored bytes).
     *
     * @return string[]
     */
    public static function hashesOf(File $file): array
    {
        return array_values(array_unique(array_filter([
            trim((string) $file->content()->get('source_hash')->value()),
            self::fileHash($file),
        ])));
    }

    /**
     * The library image with the given hash, if any.
     */
    public static function findByHash(string $hash, ?File $except = null): ?File
    {
        foreach (self::images() as $image) {
            if ($except !== null && $image->id() === $except->id()) {
                continue;
            }

            if (in_array($hash, self::hashesOf($image), true)) {
                return $image;
            }
        }

        return null;
    }

    /**
     * sha1 of the stored file, cached per path and modification time (the
     * library holds hundreds of images; hashing them on every upload would
     * be slow).
     */
    private static function fileHash(File $file): ?string
    {
        $root = $file->root();
        if (!F::exists($root)) {
            return null;
        }

        $key = 'hash-' . sha1($root . '|' . F::modified($root));

        try {
            $cache = \kirby()->cache('gs-mmh.mmh-site');
        } catch (\Throwable) {
            $cache = null;
        }

        $hash = $cache?->get($key);
        if (!is_string($hash)) {
            $hash = sha1_file($root) ?: null;
            if ($hash !== null) {
                $cache?->set($key, $hash);
            }
        }

        return $hash;
    }

    /**
     * `file.create:before`: rejects image uploads that already exist in the
     * library and remembers the hash of the original upload.
     *
     * @param object $upload Kirby's upload asset (has sha1() and type())
     */
    public static function beforeCreate(File $file, object $upload): void
    {
        if (!method_exists($upload, 'type') || $upload->type() !== 'image') {
            return;
        }

        $parent = $file->parent();
        foreach (self::IGNORED_ROOTS as $ignored) {
            if ($parent instanceof Page && ($parent->id() === $ignored || str_starts_with($parent->id(), $ignored . '/'))) {
                return;
            }
        }

        $hash = $upload->sha1();
        $existing = self::findByHash($hash);

        // Re-uploading the very same file under the same name replaces it;
        // Kirby handles that itself.
        if ($existing !== null && $existing->id() !== $file->id()) {
            throw new DuplicateException(
                'Dieses Bild liegt schon in der Mediathek: ' . self::describe($existing)
                . ' – bitte dort auswählen statt erneut hochzuladen.',
            );
        }

        self::$pendingHashes[$file->id()] = $hash;
    }

    /**
     * `file.create:after`: stores the hash of the original upload with the
     * file (library images only), so resized images are still recognised.
     */
    public static function afterCreate(File $file): void
    {
        $hash = self::$pendingHashes[$file->id()] ?? null;
        unset(self::$pendingHashes[$file->id()]);

        if ($hash === null || !self::isInLibrary($file)) {
            return;
        }

        \kirby()->impersonate('kirby', fn () => $file->save(['source_hash' => $hash]));
    }

    /**
     * `file.delete:before`: an image that is still referenced stays.
     */
    public static function guardDelete(File $file): void
    {
        if (!self::isInLibrary($file)) {
            return;
        }

        $labels = self::usageLabels($file);
        if ($labels === []) {
            return;
        }

        throw new PermissionException(
            'Das Bild wird noch verwendet in: ' . implode(', ', $labels)
            . '. Bitte zuerst dort entfernen.',
        );
    }

    /**
     * Albums of the library (for the move dialog).
     */
    public static function albums(): Pages
    {
        $root = self::root();

        return $root
            ? $root->childrenAndDrafts()->filterBy('intendedTemplate', self::ALBUM_TEMPLATE)
            : new Pages([]);
    }

    /**
     * Moves a library image into another album. File, content (all
     * versions) and UUID move along; references by UUID keep working.
     * A name that is taken in the target album gets a number appended.
     */
    public static function move(File $file, Page $album): File
    {
        if (!self::isInLibrary($file)) {
            throw new InvalidArgumentException('Nur Bilder der Mediathek lassen sich verschieben.');
        }

        if ($album->intendedTemplate()->name() !== self::ALBUM_TEMPLATE || !self::isInLibrary(new File(['filename' => 'x', 'parent' => $album]))) {
            throw new InvalidArgumentException('Das Ziel ist kein Album der Mediathek.');
        }

        if ($file->parent()->id() === $album->id()) {
            return $file;
        }

        $filename = self::freeFilename($album, $file->filename());
        $target = new File(['filename' => $filename, 'parent' => $album]);

        // media versions and the UUID cache point to the old place
        $file->unpublish();
        $file->uuid()?->clear();

        foreach ([VersionId::latest(), VersionId::changes()] as $version) {
            $from = $file->storage()->contentFiles($version);
            $to = $target->storage()->contentFiles($version);

            foreach ($from as $i => $path) {
                if (F::exists($path) && isset($to[$i])) {
                    F::move($path, $to[$i]);
                }
            }
        }

        if (!F::move($file->root(), $target->root())) {
            throw new InvalidArgumentException('Das Bild konnte nicht verschoben werden.');
        }

        $moved = new File(['filename' => $filename, 'parent' => $album]);
        $moved->uuid()?->populate();
        self::flush();

        // let git-content commit both folders (it listens to these hooks)
        \kirby()->trigger('file.changeName:after', ['newFile' => $moved, 'oldFile' => $file]);
        \kirby()->trigger('file.changeName:after', ['newFile' => $file, 'oldFile' => $file]);

        return $moved;
    }

    private static function freeFilename(Page $album, string $filename): string
    {
        $name = F::name($filename);
        $extension = F::extension($filename);
        $candidate = $filename;
        $n = 2;

        while (F::exists($album->root() . '/' . $candidate)) {
            $candidate = $name . '-' . $n . '.' . $extension;
            $n++;
        }

        return $candidate;
    }

    /**
     * Pages whose content references the file by UUID.
     */
    public static function usage(File $file): Pages
    {
        $uuid = $file->content()->get('uuid')->value();
        if (!$uuid) {
            return new Pages([]);
        }

        $ids = self::usageIndex()['file://' . $uuid] ?? [];
        $pages = [];

        foreach ($ids as $id) {
            if ($id !== 'site' && ($page = \kirby()->page($id))) {
                $pages[] = $page;
            }
        }

        return new Pages($pages);
    }

    /**
     * Whether the site settings (e.g. the default social image) use the file.
     */
    public static function usedBySite(File $file): bool
    {
        $uuid = $file->content()->get('uuid')->value();

        return $uuid && in_array('site', self::usageIndex()['file://' . $uuid] ?? [], true);
    }

    /**
     * Human readable usage for the panel.
     */
    public static function usageInfo(File $file): string
    {
        $labels = self::usageLabels($file);

        return match (count($labels)) {
            0 => 'Noch nicht verwendet.',
            1 => 'Verwendet in: ' . $labels[0],
            default => 'Verwendet an ' . count($labels) . ' Stellen: ' . implode(', ', $labels),
        };
    }

    /**
     * Titles of everything that uses the file.
     *
     * @return string[]
     */
    public static function usageLabels(File $file): array
    {
        $labels = array_map(
            fn (Page $page) => $page->title()->value(),
            self::usage($file)->values(),
        );

        if (self::usedBySite($file)) {
            $labels[] = 'Website-Einstellungen';
        }

        return $labels;
    }

    /**
     * Drops the per-request usage index (after content changes).
     */
    public static function flush(): void
    {
        self::$usageIndex = null;
    }

    /**
     * file uuid => ids of pages whose content contains it.
     *
     * @return array<string, string[]>
     */
    private static function usageIndex(): array
    {
        if (self::$usageIndex !== null) {
            return self::$usageIndex;
        }

        $index = [];
        $site = \kirby()->site();

        foreach ([$site, ...$site->index(true)->values()] as $model) {
            $raw = implode("\n", array_map(
                fn ($value) => is_scalar($value) ? (string) $value : (string) json_encode($value),
                $model->content()->toArray(),
            ));

            if (preg_match_all('!file://[A-Za-z0-9_-]+!', $raw, $matches)) {
                foreach (array_unique($matches[0]) as $reference) {
                    // The site has no page id; '' resolves to the home page
                    // in Kirby, so it is stored as 'site'.
                    $index[$reference][] = $model instanceof Page ? $model->id() : 'site';
                }
            }
        }

        return self::$usageIndex = $index;
    }

    private static function describe(File $file): string
    {
        $album = $file->parent() instanceof Page ? $file->parent()->title()->value() : '';
        $label = $file->content()->get('alt')->value() ?: $file->filename();

        return trim($album . ' / ' . $label, ' /');
    }
}
