(function($) {
  'use strict';

  $(document).ready(function() {
    $('#wga-verify-opt-code').on('click', function(event) {
      event.preventDefault();
      var $btn = $(event.target);
      $btn.html('Verifying .... ');

      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'verify_otp_before_enable',
          otp_code: $('#wiloke_ga_opt_verification').val(),
          user_id: $('input[name="checkuser_id"]').val()
        },
        success: function(response) {
          alert(response.data.msg);
          if (response.success) {
            $('#wiloke_ga_mode').find('option[value="enable"]').prop('selected', true);
            $btn.remove();
          } else {
            $btn.html('Verify OTP Code');
          }
        }
      });
    });
  });
})(jQuery);
