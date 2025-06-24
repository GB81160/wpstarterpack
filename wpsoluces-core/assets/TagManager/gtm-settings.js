/**
 * WPSoluces – validation GTM
 * Bloque la sauvegarde si :
 *   • case « Activer » cochée et ID vide
 *   • ID ne correspond pas à GTM-XXXXXXX (7 alphanum)
 */

document.addEventListener('DOMContentLoaded', () => {

  const idField   = document.querySelector('#wpsc_gtm_id');
  const activeBox = document.querySelector('#wpsc_gtm_active');
  const form      = idField?.closest('form');

  if (!idField || !activeBox || !form) { return; }

  const GTM = /^GTM-[A-Z0-9]{7}$/i;

  form.addEventListener('submit', e => {

    idField.value = idField.value.trim().toUpperCase();

    if (!activeBox.checked) {           // injection désactivée → aucune vérif
      return;
    }

    if (idField.value === '') {
      alert('Veuillez saisir un ID GTM (ex. : GTM-ABC1234).');
      e.preventDefault();
      return;
    }

    if (!GTM.test(idField.value)) {
      alert('ID GTM invalide : il doit respecter exactement le format « GTM-XXXXXXX ».');
      e.preventDefault();
    }
  });
});
