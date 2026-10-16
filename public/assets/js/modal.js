/* Modal behavior for every <dialog class="gs-c-modal"> (snippet shared/modal).
   Declarative, no inline handlers:
     [data-modal-open="<id>"]       opens the dialog with that id
     [data-modal-close]             closes the dialog it sits in
     dialog[data-modal-auto-open]   opens on load, value = delay in ms
   Backdrop click and ESC close the dialog; focus returns to the opener.
   window.mmhModal.open(dialog) / .close(dialog) for scripts. */
(() => {
  let opener = null;

  const open = (dialog, trigger) => {
    if (!dialog || dialog.open) return;
    opener = trigger || document.activeElement;

    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
    } else {
      dialog.setAttribute('open', '');
    }

    const focusTarget = dialog.querySelector(
      '[data-modal-focus], input:not([type="hidden"]), select, textarea'
    );
    if (focusTarget) focusTarget.focus();
  };

  const close = dialog => {
    if (dialog && dialog.open) dialog.close();
  };

  // Legacy trigger of the project forms (writer mark "Link" in the web plugin)
  const legacyFormModalId = trigger => {
    const href = trigger.getAttribute('href') || '';
    if (href.startsWith('#')) return href.slice(1);

    const formId = trigger.getAttribute('data-form-modal-trigger') || '';
    return `form-modal-${formId.replace(/[^a-zA-Z0-9_-]+/g, '-')}`;
  };

  document.addEventListener('click', event => {
    const target = event.target;
    if (!target.closest) return;

    const trigger = target.closest(
      '[data-modal-open], [data-form-modal-trigger]'
    );
    if (trigger) {
      const id = trigger.hasAttribute('data-modal-open')
        ? trigger.getAttribute('data-modal-open')
        : legacyFormModalId(trigger);
      const dialog = document.getElementById(id);
      if (dialog) {
        event.preventDefault();
        open(dialog, trigger);
      }
      return;
    }

    const closer = target.closest('[data-modal-close]');
    if (closer) {
      close(closer.closest('dialog'));
      return;
    }

    // Click on the dialog itself (its backdrop area), not on its content
    if (target.matches('dialog.gs-c-modal')) {
      close(target);
    }
  });

  // close does not bubble: listen in the capture phase
  document.addEventListener(
    'close',
    event => {
      if (!event.target.matches('dialog.gs-c-modal')) return;
      if (opener && typeof opener.focus === 'function') opener.focus();
      opener = null;
    },
    true
  );

  document.querySelectorAll('dialog[data-modal-auto-open]').forEach(dialog => {
    if (dialog.open) return;
    const delay = Number(dialog.getAttribute('data-modal-auto-open')) || 0;
    window.setTimeout(() => open(dialog), delay);
  });

  window.mmhModal = { open, close };
})();
