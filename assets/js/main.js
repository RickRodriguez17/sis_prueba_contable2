/* ============================================================
   Validaciones de cliente del Sistema de Contabilidad
   ============================================================ */

// ---------- Validación del código de cuenta (8 dígitos numéricos) ----------
document.addEventListener('DOMContentLoaded', function () {
    const codigoInputs = document.querySelectorAll('input[data-validate="codigo-cuenta"]');
    codigoInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            // Solo permitir dígitos
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
            const errorBox = document.getElementById(this.dataset.errorTarget || '');
            if (errorBox) {
                if (this.value === '') {
                    errorBox.textContent = '';
                } else if (this.value.length !== 8) {
                    errorBox.textContent = 'El código debe tener exactamente 8 dígitos.';
                } else {
                    errorBox.textContent = '';
                }
            }
        });
    });

    // Validación del formulario al enviar
    const formCuenta = document.getElementById('form-cuenta');
    if (formCuenta) {
        formCuenta.addEventListener('submit', function (e) {
            const cod = formCuenta.querySelector('input[name="codigo"]');
            if (!cod) return;
            if (!/^[0-9]{8}$/.test(cod.value)) {
                e.preventDefault();
                alert('El código de la cuenta debe ser numérico y tener exactamente 8 dígitos.');
                cod.focus();
            }
        });
    }

    // Validación del formulario de comprobante (debe = haber)
    const formComp = document.getElementById('form-comprobante');
    if (formComp) {
        formComp.addEventListener('submit', function (e) {
            if (!recalcularTotales()) {
                e.preventDefault();
            }
        });
        formComp.addEventListener('input', function () { recalcularTotales(); });
        recalcularTotales();
    }
});

function recalcularTotales() {
    const tbody = document.querySelector('#tabla-detalle tbody');
    if (!tbody) return true;
    let totalDebe = 0, totalHaber = 0;
    tbody.querySelectorAll('tr.detalle-row').forEach(function (row) {
        const debe = parseFloat(row.querySelector('input[name="debe[]"]').value) || 0;
        const haber = parseFloat(row.querySelector('input[name="haber[]"]').value) || 0;
        totalDebe += debe;
        totalHaber += haber;
    });
    const tdDebe = document.getElementById('total-debe');
    const tdHaber = document.getElementById('total-haber');
    const aviso = document.getElementById('aviso-balance');
    if (tdDebe) tdDebe.textContent = totalDebe.toFixed(2);
    if (tdHaber) tdHaber.textContent = totalHaber.toFixed(2);

    const balanceado = Math.abs(totalDebe - totalHaber) < 0.005 && totalDebe > 0;
    if (aviso) {
        if (totalDebe === 0 && totalHaber === 0) {
            aviso.textContent = '';
            aviso.className = '';
        } else if (balanceado) {
            aviso.textContent = 'El asiento está balanceado.';
            aviso.className = 'alert alert-success';
        } else {
            aviso.textContent = 'El asiento NO está balanceado: debe = haber es obligatorio.';
            aviso.className = 'alert alert-error';
        }
    }
    return balanceado;
}

function agregarFilaDetalle() {
    const tbody = document.querySelector('#tabla-detalle tbody');
    if (!tbody) return;
    const plantilla = document.getElementById('plantilla-fila');
    if (!plantilla) return;
    const nueva = plantilla.content.cloneNode(true);
    tbody.appendChild(nueva);
    recalcularTotales();
}

function eliminarFila(btn) {
    const fila = btn.closest('tr.detalle-row');
    if (!fila) return;
    const tbody = fila.parentNode;
    fila.remove();
    if (tbody.querySelectorAll('tr.detalle-row').length === 0) {
        agregarFilaDetalle();
    }
    recalcularTotales();
}
