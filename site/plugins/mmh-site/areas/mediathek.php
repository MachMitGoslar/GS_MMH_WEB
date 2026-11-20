<?php

/**
 * Mediathek in the panel: a view button "In Album verschieben" on library
 * images (files/mediathek-image.yml → buttons) and its dialog. Kirby itself
 * cannot move files between pages; Mediathek::move() does.
 */

use Kirby\Cms\File;
use Kirby\Exception\NotFoundException;
use Mmh\Site\Mediathek;

return function () {
    $find = function (string $uuid): File {
        $file = kirby()->file('file://' . $uuid);

        if (!$file instanceof File || !Mediathek::isInLibrary($file)) {
            throw new NotFoundException('Bild nicht gefunden');
        }

        return $file;
    };

    return [
        'buttons' => [
            'file.mediathek-move' => function (File $file) {
                if (!Mediathek::isInLibrary($file) || !$file->uuid()) {
                    return null;
                }

                return [
                    'icon' => 'folder',
                    'text' => 'In Album verschieben',
                    'dialog' => 'mediathek-move/' . $file->uuid()->id(),
                ];
            },
        ],
        'dialogs' => [
            'mediathek-move/(:any)' => [
                'load' => function (string $uuid) use ($find) {
                    $file = $find($uuid);
                    $options = [];

                    foreach (Mediathek::albums() as $album) {
                        $options[] = [
                            'value' => $album->id(),
                            'text' => $album->title()->value(),
                            'disabled' => $album->id() === $file->parent()->id(),
                        ];
                    }

                    return [
                        'component' => 'k-form-dialog',
                        'props' => [
                            'fields' => [
                                'album' => [
                                    'label' => 'Album',
                                    'type' => 'radio',
                                    'columns' => 2,
                                    'required' => true,
                                    'options' => $options,
                                    'help' => 'Verweise auf das Bild bleiben erhalten.',
                                ],
                            ],
                            'submitButton' => [
                                'icon' => 'folder',
                                'text' => 'Verschieben',
                            ],
                            'value' => ['album' => null],
                        ],
                    ];
                },
                'submit' => function (string $uuid) use ($find) {
                    $kirby = kirby();
                    $file = $find($uuid);
                    $album = $kirby->page((string) $kirby->request()->get('album'));

                    if ($album === null) {
                        throw new NotFoundException('Album nicht gefunden');
                    }

                    if (!$file->permissions()->can('delete') || !$file->permissions()->can('changeName')) {
                        throw new Kirby\Exception\PermissionException('Keine Berechtigung zum Verschieben');
                    }

                    $moved = Mediathek::move($file, $album);

                    return [
                        'message' => 'Verschoben nach „' . $album->title()->value() . '“',
                        'redirect' => $moved->panel()->url(true),
                    ];
                },
            ],
        ],
    ];
};
