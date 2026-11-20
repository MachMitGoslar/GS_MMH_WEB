<?php

declare(strict_types=1);

namespace Mmh\Site;

/**
 * One-off migration of existing images into the Mediathek.
 *
 * Works on the content folder as plain files and never boots Kirby: Kirby
 * with the real plugins would let git-content commit into the enclosing
 * repository (see docs). Run it through scripts/mediathek-migrate.php.
 *
 * What it does:
 * - creates the Mediathek and its albums (if missing)
 * - groups identical images (sha1); per group one canonical image moves into
 *   an album, references to the other copies (`file://<uuid>`) are rewritten
 *   to the canonical image and the copies are deleted
 * - moves all images of the signage area into the album "Signage"
 * - keeps every crop: a copy whose focus differs from the canonical image
 *   passes its focus to the `<field>-focus` of the pages that used it
 *   (cover, image fields of pages); other places are listed in the report
 * - pages that relied on their first image (no cover chosen) get that image
 *   as cover, so SEO/cards/hero do not change
 *
 * plan() only reads; apply() executes a plan.
 */
final class MediathekMigration
{
    public const ALBUMS = [
        'eingang' => 'Eingang',
        'allgemein' => 'Allgemein',
        'raeume-haus' => 'Räume & Haus',
        'logos-grafik' => 'Logos & Grafik',
        'veranstaltungen' => 'Veranstaltungen',
        'projekte' => 'Projekte',
        'signage' => 'Signage',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /** Top-level areas (without number prefix) that are never touched. */
    private const EXCLUDED = ['forms', 'mediathek'];

    /** Top-level area => album. */
    private const AREA_ALBUMS = [
        'projects' => 'projekte',
        'project-archive' => 'projekte',
        'signage' => 'signage',
        'notes' => 'veranstaltungen',
        'newsletter' => 'veranstaltungen',
        'events' => 'veranstaltungen',
    ];

    /** Page fields whose focus has a companion `<field>-focus` field. */
    private const FOCUS_FIELDS = ['cover' => 'cover_focus', 'image' => 'image_focus'];

    /** @var array<string, array<string, mixed>> rel path => image info */
    private array $images = [];

    /** @var array<string, string> rel path of page txt => raw content */
    private array $pages = [];

    public function __construct(private string $root)
    {
        $this->root = rtrim($root, '/');
    }

    /**
     * Everything that would happen, without touching a file.
     *
     * @return array{actions: array<int, array<string, mixed>>, warnings: string[], stats: array<string, int>}
     */
    public function plan(): array
    {
        $this->scan();

        $actions = [];
        $warnings = [];
        $taken = [];

        foreach ($this->setupActions() as $action) {
            $actions[] = $action;
        }

        // 1. groups of identical images
        $groups = [];
        foreach ($this->images as $rel => $image) {
            $groups[$image['hash']][] = $rel;
        }

        $handled = [];
        $removedFromPage = [];

        foreach ($groups as $hash => $members) {
            if (count($members) < 2) {
                continue;
            }

            $canonical = $this->canonical($members);
            $canonicalImage = $this->images[$canonical];
            $focus = $this->commonFocus($members);
            $uuid = $canonicalImage['uuid'] ?? self::uuid();
            $target = $this->target($canonical, $taken);

            $actions[] = [
                'type' => 'move',
                'from' => $canonical,
                'to' => $target,
                'uuid' => $uuid,
                'hash' => $hash,
                'focus' => $focus,
                'group' => count($members),
            ];
            $handled[$canonical] = true;
            $removedFromPage[dirname($canonical)][] = $canonical;

            foreach ($members as $member) {
                if ($member === $canonical) {
                    continue;
                }

                $image = $this->images[$member];
                $actions[] = ['type' => 'delete', 'path' => $member, 'canonical' => $target];
                $handled[$member] = true;
                $removedFromPage[dirname($member)][] = $member;

                if (empty($image['uuid'])) {
                    continue;
                }

                foreach ($this->referencingPages($image['uuid']) as $page) {
                    $actions[] = ['type' => 'rewrite', 'txt' => $page, 'from' => $image['uuid'], 'to' => $uuid];

                    // keep the crop of this usage
                    $ownFocus = $image['focus'] ?? null;
                    if ($ownFocus !== null && $ownFocus !== $focus) {
                        $field = $this->referencingField($page, $image['uuid']);
                        if ($field !== null && isset(self::FOCUS_FIELDS[$field])) {
                            $actions[] = ['type' => 'focus', 'txt' => $page, 'field' => self::FOCUS_FIELDS[$field], 'value' => $ownFocus];
                        } else {
                            $warnings[] = "Ausschnitt prüfen: $page nutzte $member mit Fokus $ownFocus (Mediathek: " . ($focus ?? 'keiner') . ').';
                        }
                    }
                }
            }
        }

        // 2. all signage images
        foreach ($this->images as $rel => $image) {
            if (isset($handled[$rel]) || $this->area($rel) !== 'signage') {
                continue;
            }

            $actions[] = [
                'type' => 'move',
                'from' => $rel,
                'to' => $this->target($rel, $taken, 'signage'),
                'uuid' => $image['uuid'] ?? self::uuid(),
                'hash' => $image['hash'],
                'focus' => $image['focus'] ?? null,
                'group' => 1,
            ];
            $removedFromPage[dirname($rel)][] = $rel;
        }

        // 3. pages that showed their first image as cover
        foreach ($removedFromPage as $folder => $removed) {
            $page = $this->pageTxt($folder);
            // signage slides have no cover; their images are set explicitly
            // (site files in the content root have no cover either)
            if ($page === null || $folder === '.' || $this->area($folder) === 'signage' || $this->field($this->pages[$page], 'cover') !== '') {
                continue;
            }

            $first = $this->firstImage($folder);
            if ($first === null || !in_array($first, $removed, true)) {
                continue;
            }

            $uuid = $this->finalUuid($first, $actions);
            $actions[] = ['type' => 'cover', 'txt' => $page, 'uuid' => $uuid, 'image' => $first];

            if (str_contains($folder, 'newsletter')) {
                $warnings[] = "Newsletter-Teaser prüfen: $page (nutzt ohne Titelbild das letzte Bild der Seite).";
            }
        }

        $stats = [
            'images' => count($this->images),
            'groups' => count(array_filter($groups, fn ($m) => count($m) > 1)),
            'moved' => count(array_filter($actions, fn ($a) => $a['type'] === 'move')),
            'deleted' => count(array_filter($actions, fn ($a) => $a['type'] === 'delete')),
            'rewrites' => count(array_filter($actions, fn ($a) => $a['type'] === 'rewrite')),
        ];

        return ['actions' => $actions, 'warnings' => $warnings, 'stats' => $stats];
    }

    /**
     * Executes a plan from plan().
     */
    public function apply(array $plan): void
    {
        foreach ($plan['actions'] as $action) {
            match ($action['type']) {
                'setup' => $this->write($action['path'], $action['content']),
                'move' => $this->move($action),
                'delete' => $this->delete($action['path']),
                'rewrite' => $this->rewrite($action['txt'], $action['from'], $action['to']),
                'focus' => $this->setField($action['txt'], $action['field'], $action['value'], false),
                'cover' => $this->setField($action['txt'], 'cover', '- file://' . $action['uuid'], false),
            };
        }
    }

    /**
     * Markdown report of a plan.
     */
    public function report(array $plan): string
    {
        $s = $plan['stats'];
        $lines = [
            '# Mediathek-Migration',
            '',
            "- Bilder gesamt: {$s['images']}",
            "- Dubletten-Gruppen: {$s['groups']}",
            "- In die Mediathek verschoben: {$s['moved']}",
            "- Kopien gelöscht: {$s['deleted']}",
            "- Verweise umgeschrieben: {$s['rewrites']}",
            '',
        ];

        $sections = [
            'setup' => '## Anlegen',
            'move' => '## Verschieben',
            'delete' => '## Löschen (Kopien)',
            'rewrite' => '## Verweise umschreiben',
            'focus' => '## Ausschnitt übernehmen',
            'cover' => '## Titelbild setzen (bisher erstes Bild der Seite)',
        ];

        foreach ($sections as $type => $title) {
            $items = array_filter($plan['actions'], fn ($a) => $a['type'] === $type);
            if ($items === []) {
                continue;
            }

            $lines[] = $title;
            $lines[] = '';
            foreach ($items as $a) {
                $lines[] = '- ' . match ($type) {
                    'setup' => $a['path'],
                    'move' => "{$a['from']} → {$a['to']}" . ($a['group'] > 1 ? " ({$a['group']} Kopien)" : ''),
                    'delete' => "{$a['path']} (→ {$a['canonical']})",
                    'rewrite' => "{$a['txt']}: file://{$a['from']} → file://{$a['to']}",
                    'focus' => "{$a['txt']}: {$a['field']} = {$a['value']}",
                    'cover' => "{$a['txt']}: Cover = {$a['image']}",
                };
            }
            $lines[] = '';
        }

        if ($plan['warnings'] !== []) {
            $lines[] = '## Hinweise';
            $lines[] = '';
            foreach ($plan['warnings'] as $warning) {
                $lines[] = '- ' . $warning;
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    // ---------------------------------------------------------------- scan

    private function scan(): void
    {
        $this->images = [];
        $this->pages = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $rel = substr($file->getPathname(), strlen($this->root) + 1);
            $top = self::slug(explode('/', $rel)[0]);
            if (in_array($top, self::EXCLUDED, true) || str_starts_with($rel, '.git/')) {
                continue;
            }

            $ext = strtolower($file->getExtension());

            if (in_array($ext, self::IMAGE_EXTENSIONS, true)) {
                $meta = $this->read($rel . '.txt');
                $fields = self::parse($meta);
                $this->images[$rel] = [
                    'hash' => sha1_file($file->getPathname()),
                    'uuid' => $fields['uuid'] ?? null,
                    'focus' => self::validFocus($fields['focus'] ?? null),
                    'sort' => isset($fields['sort']) ? (int) $fields['sort'] : null,
                    'mtime' => $file->getMTime(),
                ];
            } elseif ($ext === 'txt' && !$this->isMetaFile($file->getPathname())) {
                $this->pages[$rel] = (string) file_get_contents($file->getPathname());
            }
        }

        ksort($this->images);
        ksort($this->pages);
    }

    private function isMetaFile(string $path): bool
    {
        // "bild.jpg.txt" belongs to "bild.jpg"
        return is_file(substr($path, 0, -4));
    }

    // ---------------------------------------------------------- decisions

    /**
     * The copy that stays: most references, then the oldest, then the path.
     */
    private function canonical(array $members): string
    {
        usort($members, function (string $a, string $b) {
            $refs = fn (string $rel) => empty($this->images[$rel]['uuid']) ? 0 : count($this->referencingPages($this->images[$rel]['uuid']));

            return [$refs($b), $this->images[$a]['mtime'], $a] <=> [$refs($a), $this->images[$b]['mtime'], $b];
        });

        return $members[0];
    }

    /**
     * Most frequent focus of the group (null if none is set).
     */
    private function commonFocus(array $members): ?string
    {
        $counts = [];
        foreach ($members as $member) {
            if ($focus = $this->images[$member]['focus']) {
                $counts[$focus] = ($counts[$focus] ?? 0) + 1;
            }
        }

        if ($counts === []) {
            return null;
        }

        arsort($counts);

        return (string) array_key_first($counts);
    }

    private function area(string $rel): string
    {
        return self::slug(explode('/', $rel)[0]);
    }

    private function target(string $rel, array &$taken, ?string $album = null): string
    {
        $album ??= self::AREA_ALBUMS[$this->area($rel)] ?? 'allgemein';
        $name = basename($rel);
        $base = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $candidate = "mediathek/$album/$name";
        $n = 2;
        while (isset($taken[$candidate]) || is_file($this->root . '/' . $candidate)) {
            $candidate = "mediathek/$album/$base-$n.$ext";
            $n++;
        }

        $taken[$candidate] = true;

        return $candidate;
    }

    /**
     * @return string[] page txt files containing the reference
     */
    private function referencingPages(string $uuid): array
    {
        $needle = 'file://' . $uuid;

        return array_keys(array_filter($this->pages, fn (string $raw) => str_contains($raw, $needle)));
    }

    /**
     * Name of the top-level field that holds the reference (null inside
     * blocks/layouts, where the reference sits in JSON).
     */
    private function referencingField(string $page, string $uuid): ?string
    {
        foreach (self::parse($this->pages[$page]) as $key => $value) {
            if (str_contains($value, 'file://' . $uuid) && !str_starts_with(ltrim($value), '[')) {
                return $key;
            }
        }

        return null;
    }

    private function pageTxt(string $folder): ?string
    {
        foreach (array_keys($this->pages) as $rel) {
            if (dirname($rel) === $folder) {
                return $rel;
            }
        }

        return null;
    }

    /**
     * First image of a folder in Kirby's default order (sort, then name).
     */
    private function firstImage(string $folder): ?string
    {
        $images = array_filter(array_keys($this->images), fn ($rel) => dirname($rel) === $folder);
        if ($images === []) {
            return null;
        }

        usort($images, fn ($a, $b) => [$this->images[$a]['sort'] ?? PHP_INT_MAX, basename($a)]
            <=> [$this->images[$b]['sort'] ?? PHP_INT_MAX, basename($b)]);

        return $images[0];
    }

    /**
     * UUID an image ends up with (deleted copies → their canonical image).
     */
    private function finalUuid(string $rel, array $actions): string
    {
        foreach ($actions as $action) {
            if ($action['type'] === 'move' && $action['from'] === $rel) {
                return $action['uuid'];
            }
            if ($action['type'] === 'delete' && $action['path'] === $rel) {
                foreach ($actions as $move) {
                    if ($move['type'] === 'move' && $move['to'] === $action['canonical']) {
                        return $move['uuid'];
                    }
                }
            }
        }

        return $this->images[$rel]['uuid'] ?? self::uuid();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function setupActions(): array
    {
        $actions = [];

        if (!is_file($this->root . '/mediathek/mediathek.txt')) {
            $actions[] = ['type' => 'setup', 'path' => 'mediathek/mediathek.txt', 'content' => self::encode(['Title' => 'Mediathek'])];
        }

        foreach (self::ALBUMS as $slug => $title) {
            $path = "mediathek/$slug/mediathek-album.txt";
            if (!is_file($this->root . '/' . $path)) {
                $actions[] = ['type' => 'setup', 'path' => $path, 'content' => self::encode(['Title' => $title])];
            }
        }

        return $actions;
    }

    // ------------------------------------------------------------ writing

    private function move(array $action): void
    {
        $from = $this->root . '/' . $action['from'];
        $to = $this->root . '/' . $action['to'];

        if (!is_dir(dirname($to))) {
            mkdir(dirname($to), 0775, true);
        }

        rename($from, $to);

        $fields = self::parse($this->read($action['from'] . '.txt'));
        if (is_file($from . '.txt')) {
            unlink($from . '.txt');
        }

        // Kirby keys are case-insensitive; normalise to the parsed (lower) keys
        $fields['uuid'] = $action['uuid'];
        $fields['template'] = 'mediathek-image';
        $fields['source_hash'] = $action['hash'];
        if ($action['focus'] !== null) {
            $fields['focus'] = $action['focus'];
        }

        $this->write($action['to'] . '.txt', self::encode($fields));
    }

    private function delete(string $rel): void
    {
        foreach ([$rel, $rel . '.txt'] as $path) {
            if (is_file($this->root . '/' . $path)) {
                unlink($this->root . '/' . $path);
            }
        }
    }

    private function rewrite(string $txt, string $from, string $to): void
    {
        $raw = $this->read($txt);
        $this->write($txt, str_replace('file://' . $from, 'file://' . $to, $raw));
    }

    /**
     * Sets a field of a page txt; with $overwrite = false an existing
     * non-empty value stays.
     */
    private function setField(string $txt, string $field, string $value, bool $overwrite): void
    {
        $raw = $this->read($txt);
        $current = $this->field($raw, $field);

        if ($current !== '' && !$overwrite) {
            return;
        }

        $key = self::key($field);
        $pattern = '/^' . preg_quote($key, '/') . ':.*?(?=\n----\s*\n|\z)/ims';

        if (preg_match($pattern, $raw)) {
            $raw = (string) preg_replace($pattern, $key . ': ' . $value, $raw, 1);
        } else {
            $raw = rtrim($raw) . "\n\n----\n\n" . $key . ': ' . $value . "\n";
        }

        $this->write($txt, $raw);
    }

    private function field(string $raw, string $field): string
    {
        return trim(self::parse($raw)[strtolower($field)] ?? '');
    }

    private function read(string $rel): string
    {
        $path = $this->root . '/' . $rel;

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function write(string $rel, string $content): void
    {
        $path = $this->root . '/' . $rel;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $content);
        if (isset($this->pages[$rel])) {
            $this->pages[$rel] = $content;
        }
    }

    // ------------------------------------------------------------ helpers

    /**
     * Kirby txt → fields (lowercase keys, `-` → `_`, like Kirby reads them).
     *
     * @return array<string, string>
     */
    public static function parse(string $raw): array
    {
        $fields = [];
        foreach (preg_split('/\n----\s*\n/', str_replace("\r\n", "\n", $raw)) as $chunk) {
            $pos = strpos($chunk, ':');
            if ($pos === false) {
                continue;
            }

            $key = strtolower(str_replace('-', '_', trim(substr($chunk, 0, $pos))));
            if ($key === '') {
                continue;
            }
            $fields[$key] = trim(substr($chunk, $pos + 1));
        }

        return $fields;
    }

    /**
     * Fields → Kirby txt.
     *
     * @param array<string, string> $fields
     */
    public static function encode(array $fields): string
    {
        $parts = [];
        foreach ($fields as $key => $value) {
            $value = trim((string) $value);
            $parts[] = self::key($key) . ':' . (str_contains($value, "\n") ? "\n\n" : ' ') . $value;
        }

        return implode("\n\n----\n\n", $parts) . "\n";
    }

    private static function key(string $field): string
    {
        return ucfirst(str_replace('_', '-', strtolower($field)));
    }

    private static function slug(string $segment): string
    {
        return (string) preg_replace('/^\d+_/', '', $segment);
    }

    private static function validFocus(?string $focus): ?string
    {
        $focus = trim((string) $focus);

        return preg_match('/^\d{1,3}(\.\d+)?% \d{1,3}(\.\d+)?%$/', $focus) ? $focus : null;
    }

    private static function uuid(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $uuid = '';
        for ($i = 0; $i < 16; $i++) {
            $uuid .= $chars[random_int(0, 35)];
        }

        return $uuid;
    }
}
