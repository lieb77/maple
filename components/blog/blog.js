((Drupal, once) => {
  Drupal.behaviors.blogPermalink = {
    attach(context) {
      // Use 'once' to prevent multiple event listeners on the same element
      const elements = once('blog-permalink', '.permalink__button', context);

      elements.forEach((button) => {
        button.addEventListener('click', async (e) => {
          e.preventDefault();

          // Construct the full URL
          const path = button.getAttribute('data-url');
          const fullUrl = window.location.origin + path;
          const toast = button.querySelector('.permalink__toast');
          const label = button.querySelector('.permalink__label');
          const originalText = label.innerText;

          try {
            await navigator.clipboard.writeText(fullUrl);

            // Visual Feedback
            button.classList.add('is-copied');
            label.innerText = 'Link Copied!';
            
            // Show toast (handled via CSS class)
            if (toast) toast.classList.add('is-visible');

            // Reset after 2 seconds
            setTimeout(() => {
              button.classList.remove('is-copied');
              label.innerText = originalText;
              if (toast) toast.classList.remove('is-visible');
            }, 2000);

          } catch (err) {
            console.error('Failed to copy: ', err);
          }
        });
      });
    }
  };
})(Drupal, once);
