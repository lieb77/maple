/**
 * @file
 * Theme toggle logic for Maple theme.
 */

(function (Drupal) {
  Drupal.behaviors.mapleThemeToggle = {
    attach: function (context) {
      // 1. Find the toggle button within the current context
      // 'once' ensures we don't attach the click listener multiple times
      const toggle = context.querySelector('#theme-toggle');
      
      if (!toggle || toggle.dataset.themeInit) {
        return;
      }

      // Mark as initialized
      toggle.dataset.themeInit = true;

      // 2. Logic to set the theme
      const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme-preference', theme);
      };

      // 3. Handle click events
      toggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyTheme(newTheme);
      });

      // 4. Initial Load: Check for saved preference or system setting
      const savedTheme = localStorage.getItem('theme-preference');
      if (savedTheme) {
        applyTheme(savedTheme);
      } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme('dark');
      }
    }
  };
})(Drupal);


/*
const storageKey = 'theme-preference';

const getColorPreference = () => {
  if (localStorage.getItem(storageKey)) return localStorage.getItem(storageKey);
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const setPreference = () => {
  localStorage.setItem(storageKey, theme.value);
  reflectPreference();
};

const reflectPreference = () => {
  document.documentElement.setAttribute('data-theme', theme.value);
};

const theme = { value: getColorPreference() };

reflectPreference();

window.onload = () => {
  reflectPreference();
  document.querySelector('#theme-toggle').addEventListener('click', () => {
    theme.value = theme.value === 'light' ? 'dark' : 'light';
    setPreference();
  });
};
*/