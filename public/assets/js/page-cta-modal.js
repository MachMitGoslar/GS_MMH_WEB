(() => {
  const openModal = dialog => {
    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
      return;
    }

    dialog.setAttribute('open', '');
  };

  document.querySelectorAll('.page-cta-modal').forEach(dialog => {
    if (dialog.open) return;

    window.setTimeout(() => openModal(dialog), 250);

    dialog.querySelectorAll('[data-cta-modal-close]').forEach(button => {
      button.addEventListener('click', () => dialog.close());
    });
  });
})();
