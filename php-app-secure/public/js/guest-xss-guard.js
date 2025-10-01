(function () {
  if (typeof document === 'undefined') {
    return;
  }

  function hasDangerousContent(value) {
    if (!value) {
      return false;
    }
    var tests = [
      /<\s*script/i,
      /javascript:/i,
      /on\w+\s*=/i
    ];
    return tests.some(function (regexp) {
      return regexp.test(value);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form[data-xss-guard="true"]');
    if (!forms.length) {
      return;
    }

    forms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        var badFields = [];
        form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function (input) {
          if (hasDangerousContent(input.value)) {
            badFields.push(input.name || input.id || 'unnamed');
          }
        });

        if (badFields.length) {
          event.preventDefault();
          alert('Ввод содержит потенциальный XSS: ' + badFields.join(', '));
        }
      });
    });
  });
})();
