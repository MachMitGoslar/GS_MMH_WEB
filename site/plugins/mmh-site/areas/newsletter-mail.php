<?php

use GsMmh\WebPlugin\NewsletterRecipients;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use Kirby\Toolkit\V;
use Mmh\Site\Newsletter\EmailHtml;
use Mmh\Site\Url;

return function (App $kirby) {
    $newsletterPage = function (string $panelId): Page {
        $page = page(str_replace('+', '/', $panelId));

        if (!$page || $page->intendedTemplate()->name() !== 'newsletter') {
            throw new Exception('Newsletter nicht gefunden');
        }

        return $page;
    };

    $newsletterText = function (Page $page): string {
        $parts = [
            $page->title()->value(),
            $page->greeting_text()->kirbytextinline()->value(),
        ];

        foreach (
            [
            'review_entries',
            'actual_entries',
            'upcomming_entries',
            'news',
            ] as $field
        ) {
            foreach ($page->{$field}()->toStructure() as $entry) {
                $parts[] = $entry->headline()->value();
                $parts[] = $entry->subheadline()->value();
                $parts[] = $entry->content_text()->kirbytextinline()->value();
            }
        }

        return trim(strip_tags(implode("\n\n", array_filter($parts))));
    };

    $mailMeta = function () use ($kirby): array {
        $from = $kirby->option('email.from', 'noreply@' . $kirby->url('host'));
        $userEmail = $kirby->user()?->email();

        return [
            'from' => $from,
            'replyTo' => V::email($userEmail) ? $userEmail : $from,
        ];
    };

    return [
        'label' => 'Newsletter-Mail',
        'icon' => 'email',
        'menu' => false,
        'buttons' => [
            'page.sendNewsletter' => function (Page $page) {
                if ($page->intendedTemplate()->name() !== 'newsletter') {
                    return null;
                }

                return [
                    'icon' => 'email',
                    'text' => 'An mich senden',
                    'dialog' => 'newsletter-mail/send/' . $page->panel()->id(),
                    'theme' => 'positive',
                ];
            },
            'page.sendNewsletterToAll' => function (Page $page) {
                if ($page->intendedTemplate()->name() !== 'newsletter') {
                    return null;
                }

                return [
                    'icon' => 'email',
                    'text' => 'An alle senden',
                    'dialog' => 'newsletter-mail/send-all/' . $page->panel()->id(),
                    'theme' => 'notice',
                ];
            },
            'page.downloadNewsletterHtml' => function (Page $page) {
                if ($page->intendedTemplate()->name() !== 'newsletter') {
                    return null;
                }

                return [
                    'icon' => 'download',
                    'text' => 'HTML herunterladen',
                    'link' => url('api/newsletter-html/' . $page->panel()->id() . '.html'),
                    'target' => '_blank',
                ];
            },
        ],
        'dialogs' => [
            'newsletter-recipients/create' => [
                'load' => function () {
                    return [
                        'component' => 'k-form-dialog',
                        'props' => [
                            'fields' => [
                                'first_name' => [
                                    'type' => 'text',
                                    'label' => 'Vorname',
                                    'required' => true,
                                ],
                                'last_name' => [
                                    'type' => 'text',
                                    'label' => 'Nachname',
                                    'required' => true,
                                ],
                                'email' => [
                                    'type' => 'email',
                                    'label' => 'E-Mail-Adresse',
                                    'required' => true,
                                ],
                            ],
                            'submitButton' => [
                                'text' => 'Speichern',
                                'icon' => 'check',
                                'theme' => 'positive',
                            ],
                        ],
                    ];
                },
                'submit' => function () use ($kirby) {
                    NewsletterRecipients::create([
                        'first_name' => $kirby->request()->get('first_name'),
                        'last_name' => $kirby->request()->get('last_name'),
                        'email' => $kirby->request()->get('email'),
                    ]);

                    return [
                        'message' => 'Empfänger wurde hinzugefügt.',
                    ];
                },
            ],
            'newsletter-recipients/(:num)' => [
                'load' => function (int $id) {
                    $recipient = NewsletterRecipients::find($id);

                    if ($recipient === null) {
                        throw new Exception('Empfänger nicht gefunden.');
                    }

                    return [
                        'component' => 'k-form-dialog',
                        'props' => [
                            'fields' => [
                                'first_name' => [
                                    'type' => 'text',
                                    'label' => 'Vorname',
                                    'required' => true,
                                ],
                                'last_name' => [
                                    'type' => 'text',
                                    'label' => 'Nachname',
                                    'required' => true,
                                ],
                                'email' => [
                                    'type' => 'email',
                                    'label' => 'E-Mail-Adresse',
                                    'required' => true,
                                ],
                            ],
                            'value' => [
                                'first_name' => $recipient['first_name'],
                                'last_name' => $recipient['last_name'],
                                'email' => $recipient['email'],
                            ],
                            'submitButton' => [
                                'text' => 'Speichern',
                                'icon' => 'check',
                                'theme' => 'positive',
                            ],
                        ],
                    ];
                },
                'submit' => function (int $id) use ($kirby) {
                    NewsletterRecipients::update($id, [
                        'first_name' => $kirby->request()->get('first_name'),
                        'last_name' => $kirby->request()->get('last_name'),
                        'email' => $kirby->request()->get('email'),
                    ]);

                    return [
                        'message' => 'Empfänger wurde aktualisiert.',
                    ];
                },
            ],
            'newsletter-recipients/(:num)/delete' => [
                'load' => function () {
                    return [
                        'component' => 'k-text-dialog',
                        'props' => [
                            'text' => 'Soll dieser Empfänger wirklich gelöscht werden?',
                            'submitButton' => [
                                'text' => 'Löschen',
                                'icon' => 'trash',
                                'theme' => 'negative',
                            ],
                        ],
                    ];
                },
                'submit' => function (int $id) {
                    NewsletterRecipients::delete($id);

                    return [
                        'message' => 'Empfänger wurde gelöscht.',
                    ];
                },
            ],
            'newsletter-mail/send-all/(:all)' => [
                'load' => function (string $panelId) use ($newsletterPage) {
                    $page = $newsletterPage($panelId);
                    $recipients = NewsletterRecipients::all();
                    $count = count($recipients);

                    return [
                        'component' => 'k-text-dialog',
                        'props' => [
                            'text' => 'Der Newsletter "' . $page->title()->value()
                                . '" wird an ' . $count . ' '
                                . ($count === 1 ? 'Empfänger' : 'Empfänger')
                                . ' verschickt.',
                            'submitButton' => [
                                'text' => 'An alle senden',
                                'icon' => 'email',
                                'theme' => 'notice',
                            ],
                        ],
                    ];
                },
                'submit' => function (string $panelId) use (
                    $kirby,
                    $newsletterPage,
                    $newsletterText,
                    $mailMeta
                ) {
                    $page = $newsletterPage($panelId);
                    $recipients = NewsletterRecipients::all();

                    if (count($recipients) === 0) {
                        throw new Exception('Es sind noch keine Empfänger eingetragen.');
                    }

                    $text = $newsletterText($page);
                    $meta = $mailMeta();
                    $sent = 0;

                    foreach ($recipients as $recipient) {
                        $unsubscribeUrl = Url::absolute(
                            'newsletter-abmelden?token=' . rawurlencode(NewsletterRecipients::unsubscribeToken($recipient)),
                        );
                        $html = EmailHtml::mobileHtml($page, [
                            'unsubscribeUrl' => $unsubscribeUrl,
                        ]);

                        $kirby->email([
                            'from' => $meta['from'],
                            'fromName' => 'MachMit!Haus',
                            'replyTo' => $meta['replyTo'],
                            'to' => $recipient['email'],
                            'subject' => 'Newsletter: ' . $page->title()->value(),
                            'body' => [
                                'html' => $html,
                                'text' => $text . "\n\nAbmelden: " . $unsubscribeUrl,
                            ],
                        ]);

                        $sent++;
                    }

                    return [
                        'message' => 'Newsletter wurde an ' . $sent . ' '
                            . ($sent === 1 ? 'Empfänger' : 'Empfänger')
                            . ' verschickt.',
                    ];
                },
            ],
            'newsletter-mail/send/(:all)' => [
                'load' => function (string $panelId) use ($kirby, $newsletterPage) {
                    $page = $newsletterPage($panelId);
                    $userEmail = $kirby->user()?->email();
                    $help = 'Der Newsletter "' . $page->title()->value()
                        . '" wird an diese Adresse verschickt.';

                    return [
                        'component' => 'k-form-dialog',
                        'props' => [
                            'fields' => [
                                'email' => [
                                    'type' => 'email',
                                    'label' => 'E-Mail-Adresse',
                                    'required' => true,
                                    'help' => $help,
                                ],
                            ],
                            'submitButton' => [
                                'text' => 'Senden',
                                'icon' => 'email',
                                'theme' => 'positive',
                            ],
                            'value' => [
                                'email' => $userEmail,
                            ],
                        ],
                    ];
                },
                'submit' => function (string $panelId) use (
                    $kirby,
                    $newsletterPage,
                    $newsletterText,
                    $mailMeta
                ) {
                    $page = $newsletterPage($panelId);
                    $email = trim((string) $kirby->request()->get('email'));

                    if (V::email($email) !== true) {
                        throw new Exception('Bitte gib eine gültige E-Mail-Adresse ein.');
                    }

                    $meta = $mailMeta();

                    $kirby->email([
                        'from' => $meta['from'],
                        'fromName' => 'MachMit!Haus',
                        'replyTo' => $meta['replyTo'],
                        'to' => $email,
                        'subject' => 'Newsletter: ' . $page->title()->value(),
                        'body' => [
                            'html' => EmailHtml::mobileHtml($page, [
                                'unsubscribeUrl' => Url::absolute('newsletter-abmelden'),
                            ]),
                            'text' => $newsletterText($page) . "\n\nAbmelden: "
                                . Url::absolute('newsletter-abmelden'),
                        ],
                    ]);

                    return [
                        'message' => 'Newsletter wurde an ' . $email . ' verschickt.',
                    ];
                },
            ],
        ],
    ];
};
