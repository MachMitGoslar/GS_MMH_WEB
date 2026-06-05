(() => {
  const modalIdFromFormId = formId => `form-modal-${String(formId || '').replace(/[^a-zA-Z0-9_-]+/g, '-')}`;
  let activeModal = null;
  let previousFocus = null;

  const closeModal = () => {
    if (!activeModal) {
      return;
    }

    activeModal.hidden = true;
    activeModal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('has-project-form-modal');

    if (previousFocus && typeof previousFocus.focus === 'function') {
      previousFocus.focus();
    }

    activeModal = null;
    previousFocus = null;
  };

  const openModal = modal => {
    previousFocus = document.activeElement;
    activeModal = modal;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('has-project-form-modal');

    const focusTarget = modal.querySelector('[data-form-modal-close], input, select, textarea, button, a');

    if (focusTarget) {
      focusTarget.focus();
    }
  };

  document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-form-modal-trigger]');

    if (trigger) {
      const href = trigger.getAttribute('href') || '';
      const formId = trigger.getAttribute('data-form-modal-trigger') || '';
      const modalId = href.startsWith('#') ? href.slice(1) : modalIdFromFormId(formId);
      const modal = document.getElementById(modalId);

      if (!modal) {
        return;
      }

      event.preventDefault();
      openModal(modal);
      return;
    }

    if (event.target.closest('[data-form-modal-close]')) {
      event.preventDefault();
      closeModal();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      closeModal();
    }
  });
})();
