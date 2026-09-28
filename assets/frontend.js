/* WordCamp Certificate Generator – Frontend JS */
(function ($) {
  'use strict';

  $(document).ready(function () {
    var $form      = $('#wcc-form');
    var $wrap      = $('#wcc-form-wrap');
    var $success   = $('#wcc-success');
    var $certLink  = $('#wcc-cert-link');
    var $submit    = $('#wcc-submit');
    var $submitTxt = $submit.find('.wcc-submit-text');
    var $submitLdr = $submit.find('.wcc-submit-loading');
    var $formErr   = $('#wcc-form-error');
    var $nameInput = $('#wcc-full-name');
    var $emailInput= $('#wcc-email');
    var $nameErr   = $('#wcc-name-error');
    var $emailErr  = $('#wcc-email-error');

    // Inline validation
    $nameInput.on('blur', function () { validateName(); });
    $emailInput.on('blur', function () { validateEmail(); });
    $nameInput.on('input', function () {
      if ($(this).hasClass('wcc-input-error')) validateName();
    });
    $emailInput.on('input', function () {
      if ($(this).hasClass('wcc-input-error')) validateEmail();
    });

    function validateName() {
      var val = $nameInput.val().trim();
      if (!val) {
        setError($nameInput, $nameErr, 'Please enter your full name.');
        return false;
      }
      clearError($nameInput, $nameErr);
      return true;
    }

    function validateEmail() {
      var val = $emailInput.val().trim();
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!val) {
        setError($emailInput, $emailErr, 'Please enter your email address.');
        return false;
      }
      if (!emailRegex.test(val)) {
        setError($emailInput, $emailErr, 'Please enter a valid email address.');
        return false;
      }
      clearError($emailInput, $emailErr);
      return true;
    }

    function setError($input, $errEl, msg) {
      $input.addClass('wcc-input-error');
      $errEl.text(msg);
    }
    function clearError($input, $errEl) {
      $input.removeClass('wcc-input-error');
      $errEl.text('');
    }

    function setLoading(loading) {
      if (loading) {
        $submit.prop('disabled', true);
        $submitTxt.hide();
        $submitLdr.show();
      } else {
        $submit.prop('disabled', false);
        $submitTxt.show();
        $submitLdr.hide();
      }
    }

    $form.on('submit', function (e) {
      e.preventDefault();

      var validName  = validateName();
      var validEmail = validateEmail();
      if (!validName || !validEmail) return;

      $formErr.hide().text('');
      setLoading(true);

      $.ajax({
        url:  wccData.ajaxUrl,
        type: 'POST',
        data: {
          action:    'wcc_generate',
          nonce:     wccData.nonce,
          full_name: $nameInput.val().trim(),
          email:     $emailInput.val().trim(),
        },
        success: function (res) {
          setLoading(false);
          if (res.success) {
            $certLink.attr('href', res.data.cert_url);
            $form.fadeOut(200, function () {
              $success.fadeIn(200);
            });
          } else {
            $formErr.text(res.data.message || 'Something went wrong. Please try again.').show();
          }
        },
        error: function () {
          setLoading(false);
          $formErr.text('Network error. Please try again.').show();
        },
      });
    });

    // "Generate another" resets the form
    $('#wcc-another').on('click', function () {
      $success.fadeOut(200, function () {
        $form[0].reset();
        clearError($nameInput, $nameErr);
        clearError($emailInput, $emailErr);
        $formErr.hide().text('');
        $form.fadeIn(200);
      });
    });
  });

}(jQuery));
