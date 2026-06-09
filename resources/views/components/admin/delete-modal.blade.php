{{-- components/admin/delete-modal.blade.php --}}
@props([
    'modalId' => 'deleteModal',
    'title' => 'Confirmar Eliminación',
    'message' => '¿Estás seguro de que deseas eliminar',
    'warningText' => 'Esta acción no se puede deshacer.',
    'confirmText' => 'Eliminar',
    'cancelText' => 'Cancelar'
])

<!-- Modal de Confirmación de Eliminación -->
<div class="custom-modal" id="{{ $modalId }}">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <i class="fas fa-exclamation-triangle" style="color: var(--racing); margin-right: 8px;"></i>
                {{ $title }}
            </h5>
            <button type="button" class="custom-btn-close" data-dismiss="modal">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p style="margin-bottom: 12px; font-size: 15px;">{{ $message }} <strong id="itemName-{{ $modalId }}" style="color: var(--white);"></strong>?</p>
            <div style="background-color: rgba(229, 9, 20, 0.1); border: 1px dashed var(--racing); padding: 12px; margin-top: 16px;">
                <i class="fas fa-info-circle" style="color: var(--racing); margin-right: 8px;"></i>
                <small style="color: var(--bone);">{{ $warningText }}</small>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn ghost" data-dismiss="modal">
                {{ $cancelText }}
            </button>
            <button type="button" class="btn" style="border-color: var(--racing); color: var(--racing);" id="confirmDelete-{{ $modalId }}">
                <i class="fas fa-trash"></i> {{ $confirmText }}
            </button>
        </div>
    </div>
</div>

<!-- Formulario oculto para la eliminación -->
<form id="deleteForm-{{ $modalId }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Si no cargó jQuery, salimos para evitar errores, pero asumimos que carga el evento igual.
    const modal = document.getElementById('{{ $modalId }}');
    const form = document.getElementById('deleteForm-{{ $modalId }}');
    const itemNameSpan = document.getElementById('itemName-{{ $modalId }}');
    const confirmBtn = document.getElementById('confirmDelete-{{ $modalId }}');

    // Manejar aperturas (buscar botones con data-bs-target="#deleteModal")
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('[data-bs-toggle="modal"][data-bs-target="#{{ $modalId }}"]');
        if (toggleBtn) {
            e.preventDefault();
            const deleteUrl = toggleBtn.getAttribute('data-delete-url');
            const itemName = toggleBtn.getAttribute('data-item-name');
            if (deleteUrl) form.action = deleteUrl;
            if (itemName && itemNameSpan) itemNameSpan.textContent = itemName;
            modal.classList.add('show');
        }

        // Manejar cierres
        const dismissBtn = e.target.closest('#{{ $modalId }} [data-dismiss="modal"]');
        if (dismissBtn) {
            modal.classList.remove('show');
        }
    });

    // Cerrar al hacer click afuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });

    // Confirmar
    if(confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Eliminando...';
            form.submit();
        });
    }
});
</script>