<?php
/**
* @var \Kirby\Cms\Site $site
* @var \Kirby\Cms\Page $page
*/
?>
<?php $contentIsVisible = require kirby()->root('controllers') . '/blocks.php'; ?>
<?php $teamMembers = $page->team()->toPages(); ?>
<?php snippet('layout/head'); ?>
<?php snippet('layout/header'); ?>
<style>
  @media (min-width: 768px) {
    .project-team-strip.is-floating.is-expanded,
    .project-team-strip.is-floating:focus-within {
      direction: rtl;
      justify-content: center;
      padding: 0.5rem 1.25rem 0;
      background: transparent;
      box-shadow: none;
      backdrop-filter: none;
      transform: translateY(-3.2rem);
    }

    .project-team-strip.is-floating.is-expanded::after,
    .project-team-strip.is-floating:focus-within::after {
      content: none;
    }

    .project-team-strip.is-floating.is-expanded .project-team-member,
    .project-team-strip.is-floating:focus-within .project-team-member {
      direction: ltr;
      position: relative;
      z-index: 1;
      margin-right: 0;
      right: 0;
    }

    .project-team-strip.is-floating.is-expanded .project-team-label,
    .project-team-strip.is-floating:focus-within .project-team-label {
      direction: ltr;
      display: flex;
      position: absolute;
      left: 50%;
      right: auto;
      bottom: -4rem;
      transform: translateX(-50%);
      align-items: center;
      justify-content: center;
      min-width: 7.5rem;
      height: 3.25rem;
      margin: 0;
      padding: 0.5rem 1rem;
      border: 2px solid var(--color-fg-brand-primary);
      border-radius: 1.1rem;
      background: var(--color-white);
      color: var(--color-fg-brand-primary);
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1;
      z-index: 1;
      pointer-events: none;
      box-sizing: border-box;
      white-space: nowrap;
    }
  }
</style>
  <main>
  <div class="mb-4">
    <?=snippet('sections/hero')?>
  </div>
  <section class="grid content">
    <div class="grid-item" data-span="1/1">

      <div class="project-title-wrapper">
        <h1 class="font-titleXXL project-title">
          <?= $page->headline()->isEmpty() ? $page->title() : $page->headline() ?>
        </h1>

        <h2 class="font-titleXL font-weight-light">
          <?= $page->subheadline() ?>
        </h2>

        <?php if ($teamMembers->isNotEmpty()) : ?>
          <div class="project-team-strip<?= $teamMembers->count() > 3 ? ' has-overflow' : '' ?>" aria-label="Projektteam"<?= $teamMembers->count() > 3 ? ' data-overflow-count="+' . ($teamMembers->count() - 3) . '"' : '' ?>>
            <?php foreach ($teamMembers as $member) : ?>
              <a href="<?= $member->url() ?>" class="project-team-member" title="<?= $member->title()->html() ?>">
                <span class="project-team-avatar">
                  <?php if ($memberImage = $member->cover()) : ?>
                    <img src="<?= $memberImage->crop(160, 160)->url() ?>" alt="<?= $member->title()->html() ?>">
                  <?php else : ?>
                    <span class="project-team-placeholder">
                      <?= strtoupper(substr($member->title()->value(), 0, 1)) ?>
                    </span>
                  <?php endif ?>
                </span>
                <span class="project-team-meta">
                  <span class="project-team-name"><?= $member->title()->html() ?></span>
                </span>
              </a>
            <?php endforeach ?>
            <h2 class="project-team-label">#Team</h2>
          </div>
        <?php endif ?>
      </div>

    </div>

    <div id="project-description" class="grid-item" data-span="<?= $page->project_steps()->isNotEmpty() ? '2/3' : '1/1' ?>">
        <h3 class="font-headline"> Projektbeschreibung</h3>
        <div class="designer">
        <?php foreach ($page->text()->toLayouts() as $layout) : ?>
          <?php if (!$contentIsVisible($layout)) {
              continue;
          } ?>
          <div class="grid project-layout-grid">

            <?php foreach ($layout->columns() as $column) : ?>
            <div class="grid-item" data-span="<?=$column->width()?>">

                <?php foreach ($column->blocks() as $block) : ?>
                <?php if (!$contentIsVisible($block)) {
                    continue;
                } ?>
                <div id="<?= $block->id() ?>" class="c-blog c-blog-<?= $block->type() ?>">
                    <?= $block ?>
                </div>
                <?php endforeach ?>
            </div>

            <?php endforeach ?>
          </div>

        <?php endforeach ?>
        </div>
        <section>
            <?php snippet('dreamform/forms', ['page' => $page]) ?>
        </section>
    </div>



      <?php if ($page->project_steps()->isNotEmpty()) : ?>
    <div id="timeline" class="grid-item" data-span="1/3">
            <?php snippet(name: 'content-types/projects/projectTimeline', data: ['project_steps' => $page->project_steps()]) ?>
    </div>
      <?php endif ?>

  </section>
  <section>
  </section>
<?php if ($teamMembers->isNotEmpty()) : ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const teamStrip = document.querySelector('.project-team-strip');
    const footer = document.querySelector('.footer');
    if (!teamStrip) {
      return;
    }

    const mediaQuery = window.matchMedia('(min-width: 768px)');
    const originalParent = teamStrip.parentNode;
    const originalNextSibling = teamStrip.nextSibling;
    const teamMembersList = Array.from(teamStrip.querySelectorAll('.project-team-member'));
    const teamCount = teamMembersList.length;
    const primaryTriggerMember = teamStrip.querySelector('.project-team-member');
    let ticking = false;
    let isFloating = false;
    let collapseTimer = null;

    const getScrollTop = () => {
      return window.scrollY || window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    };

    const expandTeamStrip = () => {
      if (teamCount <= 1) {
        return;
      }

      if (collapseTimer) {
        window.clearTimeout(collapseTimer);
        collapseTimer = null;
      }

      teamStrip.classList.add('is-expanded');
    };

    const collapseTeamStrip = () => {
      if (collapseTimer) {
        window.clearTimeout(collapseTimer);
      }

      collapseTimer = window.setTimeout(() => {
        teamStrip.classList.remove('is-expanded');
      }, 140);
    };

    const updateHoverRows = () => {
      const tileSize = 120;
      const tileGap = 12;
      const viewportPaddingTop = 24;
      const viewportPaddingBottom = 24;
      const availableHeight = window.innerHeight - viewportPaddingTop - viewportPaddingBottom;
      const computedRows = Math.floor((availableHeight + tileGap) / (tileSize + tileGap));
      const hoverRows = Math.max(2, Math.min(teamCount, computedRows));
      const hoverColumns = Math.max(1, Math.ceil(teamCount / hoverRows));

      teamStrip.style.setProperty('--project-team-hover-rows', String(hoverRows));
      teamStrip.style.setProperty('--project-team-hover-columns', String(hoverColumns));
      teamStrip.style.setProperty(
        '--project-team-hover-height',
        `calc((${hoverRows} * 7.5rem) + (${Math.max(hoverRows - 1, 0)} * 0.75rem))`
      );
      teamStrip.style.setProperty(
        '--project-team-hover-width',
        `calc((${hoverColumns} * 7.5rem) + (${Math.max(hoverColumns - 1, 0)} * 0.75rem))`
      );
    };

    teamMembersList.forEach((member, index) => {
      member.style.setProperty('--expanded-order', String(teamCount - index));
    });

    const mountFloatingStrip = () => {
      if (teamStrip.parentNode !== document.body) {
        document.body.appendChild(teamStrip);
      }

      teamStrip.classList.add('is-floating');
      teamStrip.style.position = 'fixed';
      teamStrip.style.right = '1.5rem';
      teamStrip.style.bottom = '1.5rem';
      teamStrip.style.left = 'auto';
      teamStrip.style.top = 'auto';
      teamStrip.style.zIndex = '999';
      teamStrip.style.transform = '';
      isFloating = true;
    };

    const mountInlineStrip = () => {
      if (!isFloating && teamStrip.parentNode === originalParent) {
        return;
      }

      teamStrip.classList.remove('is-floating');
      teamStrip.classList.remove('is-expanded');
      teamStrip.style.position = '';
      teamStrip.style.right = '';
      teamStrip.style.bottom = '';
      teamStrip.style.left = '';
      teamStrip.style.top = '';
      teamStrip.style.zIndex = '';
      teamStrip.style.transform = '';

      if (teamStrip.parentNode !== originalParent) {
        originalParent.insertBefore(teamStrip, originalNextSibling);
      }

      isFloating = false;
    };

    const updateTeamStripState = () => {
      ticking = false;

      if (!mediaQuery.matches) {
        mountInlineStrip();
        return;
      }

      if (getScrollTop() > 1) {
        mountFloatingStrip();
        if (footer) {
          const footerRect = footer.getBoundingClientRect();
          const footerOverlap = window.innerHeight - footerRect.top;
          const footerGap = 16;
          const viewportBottomOffset = 24;
          const stopOffset = footerOverlap - viewportBottomOffset + footerGap;

          if (stopOffset > 0) {
            teamStrip.style.transform = `translateY(-${stopOffset}px)`;
          } else {
            teamStrip.style.transform = '';
          }
        }
      } else {
        mountInlineStrip();
      }
    };

    const onScroll = () => {
      if (ticking) {
        return;
      }

      ticking = true;
      window.requestAnimationFrame(updateTeamStripState);
    };

    updateTeamStripState();
    updateHoverRows();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', updateHoverRows, { passive: true });
    document.addEventListener('scroll', onScroll, { passive: true, capture: true });
    teamStrip.addEventListener('mouseleave', collapseTeamStrip);
    teamStrip.addEventListener('focusout', (event) => {
      if (event.relatedTarget && teamStrip.contains(event.relatedTarget)) {
        return;
      }

      collapseTeamStrip();
    });

    if (primaryTriggerMember && teamCount > 1) {
      primaryTriggerMember.addEventListener('mouseenter', expandTeamStrip);
      primaryTriggerMember.addEventListener('focusin', expandTeamStrip);
    }

    if (typeof mediaQuery.addEventListener === 'function') {
      mediaQuery.addEventListener('change', updateTeamStripState);
      mediaQuery.addEventListener('change', updateHoverRows);
    } else if (typeof mediaQuery.addListener === 'function') {
      mediaQuery.addListener(updateTeamStripState);
      mediaQuery.addListener(updateHoverRows);
    }
  });
</script>
<?php endif ?>
<?php snippet('layout/footer'); ?>
<?php snippet('layout/foot'); ?>
