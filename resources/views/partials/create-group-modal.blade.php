<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="groupName" class="form-label">Nombre del grupo</label>
                    <input type="text" id="groupName" class="form-control" placeholder="Ej. Equipo de desarrollo">
                </div>

                <div class="mb-3">
                    <label for="groupDescription" class="form-label">Descripción (opcional)</label>
                    <input type="text" id="groupDescription" class="form-control" placeholder="Descripción breve">
                </div>

                <div class="mb-2">
                    <label for="groupUserSearch" class="form-label">Buscar usuarios</label>
                    <input type="text" id="groupUserSearch" class="form-control" placeholder="Escribe nombre o correo">
                </div>

                <div id="groupUserResults" class="list-group mt-2" style="max-height: 220px; overflow-y: auto; display:none;"></div>

                <div class="mt-3">
                    <label class="form-label">Miembros seleccionados</label>
                    <div id="selectedGroupUsers" class="d-flex flex-wrap gap-2"></div>
                    <small class="text-muted d-block mt-2">Tú se agregarás automáticamente como administrador.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-chat" id="createGroupBtn" type="button">Crear</button>
            </div>
        </div>
    </div>
</div>