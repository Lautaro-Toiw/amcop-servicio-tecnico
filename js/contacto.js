// Formulario de contacto: se envia por detras y el resultado se muestra en el mismo formulario,
// sin salir de la pagina. Si este archivo no carga, el formulario se envia como siempre.
(function () {
  var form = document.querySelector('.contact-form');
  if (!form || !window.fetch || !window.FormData) return;

  var button = form.querySelector('button[type="submit"]');
  var status = form.querySelector('.form-status');
  var buttonText = button.textContent;

  function show(ok, text) {
    status.textContent = text;
    status.className = 'form-status ' + (ok ? 'is-success' : 'is-error');
    status.hidden = false;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    button.disabled = true;
    button.textContent = 'Enviando…';
    status.hidden = true;

    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { Accept: 'application/json' },
    })
      .then(function (response) {
        return response.text().then(function (text) {
          try {
            return JSON.parse(text);
          } catch (error) {
            // Una respuesta que no es JSON (por ejemplo, la version anterior del PHP)
            return { ok: response.ok && /correctamente/i.test(text), mensaje: text };
          }
        });
      })
      .then(function (result) {
        if (result.ok) {
          form.reset();
          show(true, result.mensaje || '¡Gracias! Recibimos tu consulta.');
        } else {
          show(false, result.mensaje || 'No pudimos enviar tu consulta. Probá de nuevo.');
        }
      })
      .catch(function () {
        show(false, 'No pudimos enviar tu consulta: revisá la conexión y probá de nuevo.');
      })
      .then(function () {
        button.disabled = false;
        button.textContent = buttonText;
      });
  });
})();
