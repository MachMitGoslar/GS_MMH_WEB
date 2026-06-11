(() => {
  const modalIdFromFormId = formId => `form-modal-${String(formId || '').replace(/[^a-zA-Z0-9_-]+/g, '-')}`;
  let previousFocus = null;

  const closeModal = dialog => {
    if (!dialog) return;
    dialog.close();
    document.documentElement.classList.remove('has-project-form-modal');
    if (previousFocus && typeof previousFocus.focus === 'function') {
      previousFocus.focus();
    }
    previousFocus = null;
  };

  const openModal = dialog => {
    previousFocus = document.activeElement;
    dialog.showModal();
    document.documentElement.classList.add('has-project-form-modal');

    const focusTarget = dialog.querySelector('input, select, textarea, button, a');
    if (focusTarget) focusTarget.focus();
  };

  document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-form-modal-trigger]');
    if (trigger) {
      const href = trigger.getAttribute('href') || '';
      const formId = trigger.getAttribute('data-form-modal-trigger') || '';
      const modalId = href.startsWith('#') ? href.slice(1) : modalIdFromFormId(formId);
      const dialog = document.getElementById(modalId);
      if (!dialog) return;
      event.preventDefault();
      openModal(dialog);
      return;
    }

    if (event.target.closest('[data-form-modal-close]')) {
      event.preventDefault();
      const dialog = event.target.closest('dialog');
      closeModal(dialog);
      return;
    }

    // backdrop click (click lands directly on <dialog>)
    const dialog = event.target.closest('[data-form-modal-root] dialog');
    if (dialog && event.target === dialog) {
      closeModal(dialog);
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      const dialog = document.querySelector('.project-form-modal[open]');
      if (dialog) {
        event.preventDefault();
        closeModal(dialog);
      }
    }
  });
})();
