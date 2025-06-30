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
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-danger">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">{{ $message }} <strong id="itemName-{{ $modalId }}"></strong>?</p>
                <div class="d-flex align-items-center p-3 rounded" style="background-color: hsl(var(--destructive) / 0.1); border: 1px solid hsl(var(--destructive) / 0.2);">
                    <i class="fas fa-info-circle me-2" style="color: hsl(var(--destructive));"></i>
                    <small style="color: hsl(var(--destructive));">
                        {{ $warningText }}
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary-modern" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ $cancelText }}
                </button>
                <button type="button" class="btn-modern btn-destructive-modern" id="confirmDelete-{{ $modalId }}">
                    <i class="fas fa-trash me-1"></i>
                    {{ $confirmText }}
                </button>
            </div>
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
    const deleteModal = document.getElementById('{{ $modalId }}');
    const confirmDeleteBtn = document.getElementById('confirmDelete-{{ $modalId }}');
    const deleteForm = document.getElementById('deleteForm-{{ $modalId }}');
    const itemNameSpan = document.getElementById('itemName-{{ $modalId }}');
    
    // Cuando se abre el modal
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const deleteUrl = button.getAttribute('data-delete-url');
        const itemName = button.getAttribute('data-item-name');
        
        // Actualizar el formulario con la URL correcta
        deleteForm.action = deleteUrl;
        
        // Actualizar el nombre del elemento en el modal
        itemNameSpan.textContent = itemName;
    });
    
    // Cuando se confirma la eliminación
    confirmDeleteBtn.addEventListener('click', function() {
        // Agregar estado de carga al botón
        confirmDeleteBtn.classList.add('btn-loading');
        confirmDeleteBtn.innerHTML = '<span class="me-1"></span>Eliminando...';
        
        // Enviar formulario
        deleteForm.submit();
    });
    
    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteModal.classList.contains('show')) {
            const modalInstance = bootstrap.Modal.getInstance(deleteModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    });
});
</script>