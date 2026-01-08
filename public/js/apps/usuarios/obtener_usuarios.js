// public/js/apps/usuarios/obtener_usuarios.js
(function () {
  const ENDPOINT_USUARIOS = "public/php/usuarios/obtener_usuarios.php";
  const ENDPOINT_DIAG = "public/php/usuarios/obtener_diagnostico.php";

  async function fetchUsuarios() {
    const res = await fetch(ENDPOINT_USUARIOS, { credentials: "same-origin" });
    return await res.json();
  }

  async function fetchDiagnosticos({ depId, discId, exclId }) {
    const url =
      `${ENDPOINT_DIAG}?dep_id=${encodeURIComponent(depId ?? "")}` +
      `&disc_id=${encodeURIComponent(discId ?? "")}` +
      `&excl_id=${encodeURIComponent(exclId ?? "")}`;

    const res = await fetch(url, { credentials: "same-origin" });
    return await res.json();
  }

  window.OrtzadarUsuariosAPI = window.OrtzadarUsuariosAPI || {};
  window.OrtzadarUsuariosAPI.fetchUsuarios = fetchUsuarios;
  window.OrtzadarUsuariosAPI.fetchDiagnosticos = fetchDiagnosticos;
})();
