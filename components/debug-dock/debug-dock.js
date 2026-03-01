(function (Drupal, once) {
  Drupal.behaviors.mapleDebugDock = {
    attach: function (context) {
      once('debug-dock-dismiss', '#dismiss-debug', context).forEach(function (el) {
        el.addEventListener('click', () => {
          document.getElementById('debug-dock').style.display = 'none';
        });
      });
    }
  };
})(Drupal, once);