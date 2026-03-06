(function (Drupal) {
  Drupal.behaviors.mapleThemeToggle = {
    attach: function (context) {
      const toggle = context.querySelector('#theme-toggle');
      if (!toggle || toggle.dataset.themeInit) return;
      toggle.dataset.themeInit = true;

      // 1. On Load: Only apply the attribute if the user HAS a saved preference.
      // If savedTheme is null, we do NOT set data-theme at all.
      const savedTheme = localStorage.getItem('theme-preference');
      if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
      }

      toggle.addEventListener('click', () => {
        const currentAttr = document.documentElement.getAttribute('data-theme');
        let newTheme;

        if (!currentAttr) {
          // If in "Auto" mode, flip to the opposite of the current system state
          const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          newTheme = isSystemDark ? 'light' : 'dark';
        } else {
          newTheme = currentAttr === 'light' ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme-preference', newTheme);
      });
    }
  };
})(Drupal);