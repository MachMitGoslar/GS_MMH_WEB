(() => {
  const modalIdFromFormId = formId => `form-modal-${String(formId || '').replace(/[^a-zA-Z0-9_-]+/g, '-')}`;
  let previousFocus = null;

  const openModal = dialog => {
    previousFocus = document.activeElement;
    dialog.showModal();
    document.documentElement.classList.add('has-project-form-modal');

    const focusTarget = dialog.querySelector('input, select, textarea, button, a');
    if (focusTarget) focusTarget.focus();
  };

  // Use native close event for cleanup — fires regardless of how the dialog closes
  // (button onclick, ESC key, backdrop click, or dialog.close() call)
  document.querySelectorAll('.project-form-modal').forEach(dialog => {
    dialog.addEventListener('close', () => {
      document.documentElement.classList.remove('has-project-form-modal');
      if (previousFocus && typeof previousFocus.focus === 'function') {
        previousFocus.focus();
      }
      previousFocus = null;
    });
  });

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
    }
  });
})();
