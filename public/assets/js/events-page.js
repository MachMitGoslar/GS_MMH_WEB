(function () {
  const eventsPage = document.querySelector('[data-events-page]');
  const eventsDataEl = document.querySelector('#events-page-data');

  if (!eventsPage || !eventsDataEl) {
    return;
  }

  const payload = JSON.parse(eventsDataEl.textContent || '{}');
  const apiUrl = payload.apiUrl || '/api/events';
  let pageEvents = Array.isArray(payload.events) ? payload.events : [];
  let eventsError = payload.error || '';
  const todayKey = payload.today || '';
  let selectedDay = payload.selectedDay || eventsPage.dataset.selectedDay || '';
  let calendarMonth = selectedDay || todayKey;
  let activeCategory = eventsPage.dataset.activeCategory || 'all';
  let currentPage = Math.max(
    1,
    Number.parseInt(
      new URL(window.location.href).searchParams.get('page') || '1',
      10
    ) || 1
  );
  const pageSize = 12;
  const resultsList = eventsPage.querySelector('[data-events-results]');
  const resultsCount = eventsPage.querySelector('[data-results-count]');
  const resultsTitle = eventsPage.querySelector('[data-results-title]');
  const pagination = eventsPage.querySelector('[data-events-pagination]');
  const paginationPrev = eventsPage.querySelector('[data-pagination-prev]');
  const paginationNext = eventsPage.querySelector('[data-pagination-next]');
  const filtersRow = eventsPage.querySelector('.events-filter-row');
  let paginationState = {
    hasPrev: currentPage > 1,
    hasNext: Boolean(paginationNext && !paginationNext.hidden),
    total: null,
  };
  const modal = eventsPage.querySelector('.events-calendar-modal');
  const openButton = eventsPage.querySelector('[data-calendar-open]');
  const closeButton = eventsPage.querySelector('[data-calendar-close]');
  const titleNode = eventsPage.querySelector('[data-calendar-title]');
  const daysNode = eventsPage.querySelector('[data-calendar-days]');
  const dayChips = Array.from(eventsPage.querySelectorAll('.events-day-chip'));
  let filterPills = Array.from(
    eventsPage.querySelectorAll('[data-category-slug]')
  );
  const summaryChips = Array.from(
    eventsPage.querySelectorAll('[data-summary-chip]')
  );
  const defaultTitle = resultsTitle ? resultsTitle.textContent : '';

  const weekdayFormatter = new Intl.DateTimeFormat('de-DE', {
    weekday: 'long',
  });
  const monthFormatter = new Intl.DateTimeFormat('de-DE', { month: 'short' });
  const titleFormatter = new Intl.DateTimeFormat('de-DE', {
    month: 'long',
    year: 'numeric',
  });

  const escapeHtml = value =>
    String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');

  const formatSelectedTitle = dayKey => {
    if (!dayKey) return defaultTitle;
    const date = new Date(`${dayKey}T12:00:00`);
    const weekday = weekdayFormatter.format(date);
    const month = monthFormatter.format(date);
    return `${weekday.charAt(0).toUpperCase()}${weekday.slice(1)}, ${String(date.getDate()).padStart(2, '0')}. ${month.charAt(0).toUpperCase()}${month.slice(1)}`;
  };

  const renderEvent = event => {
    return `
      <li class="eventsListItem">
        <a href="${escapeHtml(event.url)}" target="_blank" rel="noopener noreferrer">
          <time class="font-footnote mb-2">${escapeHtml(event.list_time_label)}</time>
          <h3 class="font-subheadline mb-2">${escapeHtml(event.title)}</h3>
          <p class="font-body mb-2">${escapeHtml(event.description)}</p>
        </a>
      </li>
    `;
  };

  const ensureEmptyState = () => {
    let emptyState = eventsPage.querySelector('[data-events-empty]');
    if (emptyState) return emptyState;

    emptyState = document.createElement('div');
    emptyState.className = 'events-empty-state';
    emptyState.setAttribute('data-events-empty', '');
    emptyState.innerHTML = [
      '<h3 class="font-headline mb-2" data-events-empty-title>Keine passenden Termine gefunden</h3>',
      '<p class="font-body mb-3" data-events-empty-message>Passe Suchbegriff, Datum oder Kategorie an, um wieder mehr Veranstaltungen zu sehen.</p>',
    ].join('');

    resultsList?.insertAdjacentElement('afterend', emptyState);
    return emptyState;
  };

  const buildPageHref = page => {
    const url = new URL(window.location.href);
    if (selectedDay) url.searchParams.set('day', selectedDay);
    else url.searchParams.delete('day');
    if (activeCategory && activeCategory !== 'all')
      url.searchParams.set('category', activeCategory);
    else url.searchParams.delete('category');
    if (!selectedDay && page > 1) url.searchParams.set('page', String(page));
    else url.searchParams.delete('page');
    url.searchParams.delete('calendar');
    url.searchParams.delete('calendar_month');
    return `${url.pathname}${url.search}`;
  };

  const buildCategoryHref = category => {
    const url = new URL(window.location.href);
    url.searchParams.delete('page');
    url.searchParams.delete('calendar');
    url.searchParams.delete('calendar_month');

    if (category && category !== 'all') url.searchParams.set('category', category);
    else url.searchParams.delete('category');

    if (selectedDay) url.searchParams.set('day', selectedDay);
    else url.searchParams.delete('day');

    return `${url.pathname}${url.search}`;
  };

  const updateHistory = () => {
    window.history.replaceState({}, '', buildPageHref(currentPage));
  };

  const buildApiUrl = page => {
    const url = new URL(apiUrl, window.location.origin);
    const keyword = new URL(window.location.href).searchParams.get('keyword') || '';

    url.searchParams.set('page', String(page));
    url.searchParams.set('per_page', String(pageSize));

    if (keyword) url.searchParams.set('keyword', keyword);
    else url.searchParams.delete('keyword');

    if (selectedDay) url.searchParams.set('day', selectedDay);
    else url.searchParams.delete('day');

    if (activeCategory && activeCategory !== 'all')
      url.searchParams.set('category', activeCategory);
    else url.searchParams.delete('category');

    return url;
  };

  const getResultsCountLabel = () => {
    if (Number.isInteger(paginationState.total) && paginationState.total > 0) {
      return `${paginationState.total} Ergebnisse`;
    }

    return `${pageEvents.length}${paginationState.hasNext ? '+' : ''} Ergebnisse`;
  };

  const renderSummary = summary => {
    if (!Array.isArray(summary)) return;

    summaryChips.forEach(chip => {
      const label = chip.dataset.summaryChip || '';
      const item = summary.find(entry => entry.label === label);
      const countNode = chip.querySelector('.events-toolbar-chip__count');

      if (item && countNode) {
        countNode.textContent = String(item.count ?? 0);
      }
    });
  };

  const renderFilters = filters => {
    if (!filtersRow || !Array.isArray(filters)) return;

    filtersRow.innerHTML = filters
      .map(filter => {
        const slug = filter.slug || 'all';
        const isActive = slug === activeCategory;

        return `
          <a
            class="events-filter-pill gs-c-btn"
            data-active="${isActive}"
            data-category-slug="${escapeHtml(slug)}"
            data-type="${isActive ? 'primary' : 'secondary'}"
            data-size="small"
            data-style="pill"
            href="${escapeHtml(buildCategoryHref(slug))}"
          >
            <span>${escapeHtml(filter.label || slug)}</span>
            <span class="events-filter-pill__count">${escapeHtml(filter.count ?? 0)}</span>
          </a>
        `;
      })
      .join('');

    filterPills = Array.from(eventsPage.querySelectorAll('[data-category-slug]'));
  };

  const syncChipStates = () => {
    filterPills.forEach(pill => {
      const isActive = (pill.dataset.categorySlug || 'all') === activeCategory;
      pill.dataset.active = String(isActive);
      pill.dataset.type = isActive ? 'primary' : 'secondary';
    });

    summaryChips.forEach(chip => {
      const chipType = chip.dataset.summaryChip || '';
      let isActive = false;

      if (chipType === 'Alle Events') {
        isActive = activeCategory === 'all' && selectedDay === '';
      } else if (chipType === 'Heute') {
        isActive = selectedDay === todayKey;
      } else if (chipType === 'Kostenlos') {
        isActive = activeCategory === 'free' && selectedDay === '';
      }

      chip.dataset.active = String(isActive);
    });

    dayChips.forEach(chip => {
      const chipDay = chip.dataset.dayKey || '';
      const isActive = selectedDay !== '' && chipDay === selectedDay;
      chip.dataset.active = String(isActive);
      chip.dataset.type = isActive ? 'primary' : 'secondary';
    });
  };

  const renderResults = () => {
    if (!resultsList || !resultsCount || !resultsTitle) return;

    resultsCount.textContent = getResultsCountLabel();
    resultsTitle.textContent = formatSelectedTitle(selectedDay);

    if (pageEvents.length > 0) {
      resultsList.innerHTML = pageEvents.map(renderEvent).join('');
      resultsList.hidden = false;
      const emptyState = eventsPage.querySelector('[data-events-empty]');
      if (emptyState) emptyState.hidden = true;
    } else {
      resultsList.innerHTML = '';
      resultsList.hidden = true;
      const emptyState = ensureEmptyState();
      const emptyTitle = emptyState.querySelector('[data-events-empty-title]');
      const emptyMessage = emptyState.querySelector('[data-events-empty-message]');

      if (eventsError) {
        if (emptyTitle) emptyTitle.textContent = 'Termine konnten nicht geladen werden';
        if (emptyMessage)
          emptyMessage.textContent = `Die Oveda-Schnittstelle meldet gerade: ${eventsError}.`;
      } else {
        if (emptyTitle) emptyTitle.textContent = 'Keine passenden Termine gefunden';
        if (emptyMessage)
          emptyMessage.textContent =
            'Passe Suchbegriff, Datum oder Kategorie an, um wieder mehr Veranstaltungen zu sehen.';
      }

      emptyState.hidden = false;
    }

    if (pagination) {
      const shouldShowPagination =
        selectedDay === '' && (paginationState.hasPrev || paginationState.hasNext);
      pagination.hidden = !shouldShowPagination;
      pagination.style.display = shouldShowPagination ? '' : 'none';

      if (paginationPrev) {
        const hasPrev = shouldShowPagination && paginationState.hasPrev;
        paginationPrev.hidden = !hasPrev;
        if (hasPrev) paginationPrev.href = buildPageHref(currentPage - 1);
      }

      if (paginationNext) {
        const hasNext = shouldShowPagination && paginationState.hasNext;
        paginationNext.hidden = !hasNext;
        if (hasNext) paginationNext.href = buildPageHref(currentPage + 1);
      }
    }

    syncChipStates();
  };

  const loadResults = async page => {
    const response = await fetch(buildApiUrl(page), {
      headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
      throw new Error(`Events API failed with ${response.status}`);
    }

    const data = await response.json();
    pageEvents = Array.isArray(data.events) ? data.events : [];
    eventsError = data.error || '';
    renderSummary(data.summary);
    renderFilters(data.filters);
    paginationState = {
      hasPrev: Boolean(data.pagination?.has_prev),
      hasNext: Boolean(data.pagination?.has_next),
      total: Number.isInteger(data.pagination?.total)
        ? data.pagination.total
        : null,
    };
    currentPage = Math.max(1, Number.parseInt(data.pagination?.page || page, 10));
    renderResults();
    updateHistory();
  };

  const buildCalendarGrid = baseDateString => {
    if (!daysNode || !titleNode) return;

    const baseDate = new Date(`${baseDateString}T12:00:00`);
    const monthStart = new Date(
      baseDate.getFullYear(),
      baseDate.getMonth(),
      1,
      12
    );
    const jsWeekday = monthStart.getDay();
    const offset = jsWeekday === 0 ? 6 : jsWeekday - 1;
    const gridStart = new Date(monthStart);
    gridStart.setDate(monthStart.getDate() - offset);

    const title = titleFormatter.format(monthStart);
    titleNode.textContent = `${title.charAt(0).toUpperCase()}${title.slice(1)}`;

    const html = [];
    for (let i = 0; i < 35; i++) {
      const date = new Date(gridStart);
      date.setDate(gridStart.getDate() + i);
      const dayKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
      const hasEvents = pageEvents.some(event => event.date_key === dayKey);
      const isCurrentMonth = date.getMonth() === monthStart.getMonth();
      const isSelected = selectedDay
        ? selectedDay === dayKey
        : todayKey === dayKey;
      const isToday = todayKey === dayKey;

      html.push(`
        <a
          class="events-calendar-modal__day"
          data-current-month="${isCurrentMonth}"
          data-selected="${isSelected}"
          data-today="${isToday}"
          data-day-key="${dayKey}"
          href="?day=${dayKey}"
        >
          <span>${date.getDate()}</span>
          ${hasEvents ? '<i></i>' : ''}
        </a>
      `);
    }

    daysNode.innerHTML = html.join('');
  };

  const setModalOpen = isOpen => {
    if (!modal) return;
    modal.dataset.open = isOpen ? 'true' : 'false';
  };

  openButton?.addEventListener('click', event => {
    event.preventDefault();
    setModalOpen(true);
    buildCalendarGrid(calendarMonth || selectedDay || todayKey);
  });

  closeButton?.addEventListener('click', event => {
    event.preventDefault();
    setModalOpen(false);
  });

  modal?.addEventListener('click', event => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;

    if (target.classList.contains('events-calendar-modal__backdrop')) {
      setModalOpen(false);
      return;
    }

    const nav = target.closest('[data-calendar-nav]');
    if (nav instanceof HTMLElement) {
      event.preventDefault();
      const direction = nav.getAttribute('data-calendar-nav');
      const current = new Date(`${calendarMonth || todayKey}T12:00:00`);
      current.setMonth(current.getMonth() + (direction === 'next' ? 1 : -1));
      calendarMonth = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-01`;
      buildCalendarGrid(calendarMonth);
      return;
    }

    const dayLink = target.closest('.events-calendar-modal__day');
    if (dayLink instanceof HTMLElement) {
      event.preventDefault();
      selectedDay = dayLink.dataset.dayKey || '';
      calendarMonth = selectedDay || calendarMonth;
      currentPage = 1;
      loadResults(1).then(() => buildCalendarGrid(calendarMonth));
      setModalOpen(false);
    }
  });

  dayChips.forEach(chip => {
    chip.addEventListener('click', event => {
      event.preventDefault();
      selectedDay = chip.dataset.dayKey || '';
      currentPage = 1;
      loadResults(1);
    });
  });

  filtersRow?.addEventListener('click', event => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;

    const pill = target.closest('[data-category-slug]');
    if (!(pill instanceof HTMLElement)) return;

    event.preventDefault();
    activeCategory = pill.dataset.categorySlug || 'all';
    eventsPage.dataset.activeCategory = activeCategory;
    currentPage = 1;
    loadResults(1).then(() =>
      buildCalendarGrid(calendarMonth || selectedDay || todayKey)
    );
  });

  summaryChips.forEach(chip => {
    chip.addEventListener('click', event => {
      event.preventDefault();
      const chipType = chip.dataset.summaryChip || '';

      if (chipType === 'Alle Events') {
        activeCategory = 'all';
        selectedDay = '';
      } else if (chipType === 'Heute') {
        selectedDay = todayKey;
      } else if (chipType === 'Kostenlos') {
        activeCategory = 'free';
        selectedDay = '';
      }

      eventsPage.dataset.activeCategory = activeCategory;
      currentPage = 1;
      loadResults(1).then(() =>
        buildCalendarGrid(calendarMonth || selectedDay || todayKey)
      );
    });
  });

  paginationPrev?.addEventListener('click', event => {
    if (paginationPrev.hidden || currentPage <= 1) return;
    event.preventDefault();
    loadResults(currentPage - 1);
  });

  paginationNext?.addEventListener('click', event => {
    if (paginationNext.hidden) return;
    event.preventDefault();
    loadResults(currentPage + 1);
  });

  loadResults(currentPage).catch(() => {
    eventsError = 'Events API nicht erreichbar';
    pageEvents = [];
    renderResults();
  });
})();
