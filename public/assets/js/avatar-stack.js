(() => {
  const stacks = document.querySelectorAll('.avatar-stack[data-reveal]');
  if (!stacks.length) return;

  if (typeof IntersectionObserver !== 'function') {
    stacks.forEach(stack => stack.classList.add('is-revealed'));
    return;
  }

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-revealed');
        obs.unobserve(entry.target);
      });
    },
    { threshold: 0.1 }
  );

  stacks.forEach(stack => observer.observe(stack));
})();
