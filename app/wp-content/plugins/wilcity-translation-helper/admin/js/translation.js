(function($) {
  'use strict';
  $(document).ready(function() {
    $('.icon-save').after(`<button class="button has-icon icon-sync-wilcity-translation" data-loco="Sync Wilcity Translation"><span>Sync Wilcity Translation</span></button>`);
    const $locoNotices = $('#loco-notices');
    $('.icon-sync-wilcity-translation').on('click', function(event) {
      event.preventDefault();
      const $this = $(this);
      $this.addClass('loco-loading');

      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          file_info: $('#loco-fs').find('[name="path"]').val(),
          action: 'wilcity_sync_translation'
        },
        success: function(response) {
          if (response.success) {
            $locoNotices.append(`<div style="padding: 20px;" class="wilcity-translation-notice notice inline notice-success">${response.data.msg}</div>`);
          } else {
            $locoNotices.append(`<div style="padding: 20px;" class="wilcity-translation-notice notice inline notice-danger">${response.data.msg}</div>`);
          }

          setTimeout(function() {
            $('.wilcity-translation-notice').remove();
          }, 60000);
        }
      }).always(function() {
        $this.removeClass('loco-loading');
      });
    });
  });
})(jQuery);
