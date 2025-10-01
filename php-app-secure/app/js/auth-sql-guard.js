/**
 * Lightweight client-side guard for login/registration forms.
 *
 * Usage: include this file and add attribute data-sql-guard="true" to forms.
 * Script scans text/password inputs for obvious SQLi signatures and blocks submit.
 */
(function attachSqlGuards() {
  var forms = document.querySelectorAll('form[data-sql-guard="true"]');
  if (!forms.length) {
    return;
  }

  var patterns = [
    /'\s*or\s*'?.*'?=/i,
    /--/,
    /;/,
    /\/\*/,
    /\b(select|union|drop|insert|delete|update)\b/i
  ];

  Array.prototype.forEach.call(forms, function(form) {
    form.addEventListener('submit', function (event) {
      var flagged = [];
      Array.prototype.forEach.call(form.querySelectorAll('input[type="text"], input[type="password"]'), function (input) {
        var value = input.value || '';
        for (var i = 0; i < patterns.length; i += 1) {
          if (patterns[i].test(value)) {
            flagged.push({ field: input.name || input.id || 'unknown', value: value });
            break;
          }
        }
      });

      if (flagged.length) {
        event.preventDefault();
        var messages = flagged.map(function(item) {
          return 'Field "' + item.field + '" contains suspicious input: ' + item.value;
        });
        alert('Form submission blocked.\n' + messages.join('\n'));
      }
    });
  });
})();
