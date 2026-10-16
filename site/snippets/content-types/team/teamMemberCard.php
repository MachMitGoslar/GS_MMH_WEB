<?php

/**
 * Card for one team member.
 *
 * @var \Kirby\Cms\Page $teamMember
 */
?>
<?php snippet('utilities/card', [
    'variant' => 'person',
    'tag' => $tag ?? 'div',
    'attrs' => [],
    'class' => 'c-teamMemberCard',
    'imageHref' => $teamMember->url(),
    'imageLabel' => $teamMember->name()->value(),
    'media' => function () use ($teamMember) {
        snippet('utilities/avatar', ['person' => $teamMember, 'size' => 200, 'loading' => 'lazy']);
    },
    'title' => $teamMember->name()->html(),
    'titleHref' => $teamMember->url(),
    'subtitle' => $teamMember->role()->isNotEmpty() ? $teamMember->role()->html() : null,
    'text' => $teamMember->description()->isNotEmpty() ? $teamMember->description()->kirbytext() : null,
    'body' => $teamMember->hasAnyContactInfo() ? function () use ($teamMember) {
        echo '<div class="contact-links">';
        if ($teamMember->email()->isNotEmpty()) {
            echo '<a href="mailto:' . esc($teamMember->email()->value()) . '" class="contact-link email" aria-label="E-Mail">';
            snippet('utilities/icon', ['name' => 'mail', 'size' => 16]);
            echo '</a>';
        }
        if ($teamMember->phone()->isNotEmpty()) {
            echo '<a href="tel:' . esc($teamMember->phone()->value()) . '" class="contact-link phone" aria-label="Telefon">';
            snippet('utilities/icon', ['name' => 'phone', 'size' => 16]);
            echo '</a>';
        }
        echo '</div>';
    } : null,
]);
