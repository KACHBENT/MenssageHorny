<script>
$(document).ready(function () {
    let searchTimeout;
    let groupSearchTimeout;
    const selectedUsers = new Map();

    const authUserId = Number(document.getElementById('authUserId')?.value || 0);
    const groupsStoreUrl = document.getElementById('groupsStoreUrl')?.value || '';
    const csrfToken = document.getElementById('csrfToken')?.value || '';

    renderSelectedGroupUsers();

    $('#searchUsers').on('keyup', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }

        $('#searchResults').html(
            '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>'
        ).show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("chat.search") }}',
                method: 'GET',
                data: { q: query },
                success: function (users) {
                    if (users && users.length > 0) {
                        let html = '<div class="list-group mt-2" style="max-height:300px;overflow-y:auto;">';

                        users.forEach(user => {
                            const avatar = user.avatar
                                ? '/storage/' + user.avatar
                                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                            html += `
                                <a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
                                    <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold text-truncate">${escapeHtml(user.name)}</h6>
                                            ${user.is_online ? '<span class="badge bg-success">En línea</span>' : ''}
                                        </div>
                                        <small class="text-muted">${escapeHtml(user.email ?? '')}</small>
                                    </div>
                                </a>
                            `;
                        });

                        html += '</div>';
                        $('#searchResults').html(html).show();
                    } else {
                        $('#searchResults').html('<div class="alert alert-info mt-2 mb-0">No se encontraron usuarios</div>').show();
                    }
                },
                error: function () {
                    $('#searchResults').html('<div class="alert alert-danger mt-2 mb-0">Error al buscar usuarios</div>').show();
                }
            });
        }, 400);
    });

    $('#groupUserSearch').on('keyup', function () {
        clearTimeout(groupSearchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#groupUserResults').hide().empty();
            return;
        }

        $('#groupUserResults').html(
            '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>'
        ).show();

        groupSearchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("chat.search") }}',
                method: 'GET',
                data: { q: query },
                success: function (users) {
                    let html = '';

                    const filteredUsers = (users || []).filter(user =>
                        parseInt(user.id) !== authUserId && !selectedUsers.has(parseInt(user.id))
                    );

                    if (filteredUsers.length === 0) {
                        $('#groupUserResults').html('<div class="alert alert-info mb-0">No hay usuarios disponibles</div>').show();
                        return;
                    }

                    filteredUsers.forEach(user => {
                        const avatar = user.avatar
                            ? '/storage/' + user.avatar
                            : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                        html += `
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center add-group-user"
                                    data-user-id="${user.id}"
                                    data-user-name="${escapeHtml(user.name)}"
                                    data-user-email="${escapeHtml(user.email ?? '')}">
                                <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                <div class="flex-grow-1 min-w-0 text-start">
                                    <div class="fw-semibold text-truncate">${escapeHtml(user.name)}</div>
                                    <small class="text-muted text-truncate d-block">${escapeHtml(user.email ?? '')}</small>
                                </div>
                                <i class="fas fa-plus text-success"></i>
                            </button>
                        `;
                    });

                    $('#groupUserResults').html(html).show();
                },
                error: function () {
                    $('#groupUserResults').html('<div class="alert alert-danger mb-0">Error al buscar usuarios</div>').show();
                }
            });
        }, 350);
    });

    $(document).on('click', '.add-group-user', function () {
        const userId = parseInt($(this).data('user-id'));
        const userName = $(this).data('user-name');
        const userEmail = $(this).data('user-email');

        if (!selectedUsers.has(userId)) {
            selectedUsers.set(userId, {
                id: userId,
                name: userName,
                email: userEmail
            });
        }

        renderSelectedGroupUsers();
        $('#groupUserSearch').trigger('keyup');
    });

    $(document).on('click', '.remove-group-user', function () {
        const userId = parseInt($(this).data('user-id'));
        selectedUsers.delete(userId);
        renderSelectedGroupUsers();
        $('#groupUserSearch').trigger('keyup');
    });

    $('#createGroupBtn').on('click', function () {
        createGroup();
    });

    $('#createGroupModal').on('hidden.bs.modal', function () {
        resetGroupModal();
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#searchUsers, #searchResults').length) {
            $('#searchResults').hide();
        }
    });

    function createGroup() {
        const name = $('#groupName').val().trim();
        const description = $('#groupDescription').val().trim();
        const userIds = Array.from(selectedUsers.keys());

        if (!name) {
            showToast('Debes escribir un nombre para el grupo', 'warning');
            return;
        }

        if (userIds.length === 0) {
            showToast('Debes seleccionar al menos un usuario', 'warning');
            return;
        }

        $('#createGroupBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando');

        fetch(groupsStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                description: description,
                user_ids: userIds
            })
        })
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            return data;
        })
        .then(data => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            showToast(data.message || 'No se pudo crear el grupo', 'error');
            $('#createGroupBtn').prop('disabled', false).html('Crear');
        })
        .catch(error => {
            console.error(error);
            showToast(error.message || 'Error al crear el grupo', 'error');
            $('#createGroupBtn').prop('disabled', false).html('Crear');
        });
    }

    function renderSelectedGroupUsers() {
        let html = `
            <span class="badge bg-success d-inline-flex align-items-center px-3 py-2">
                Tú (admin)
            </span>
        `;

        selectedUsers.forEach(user => {
            html += `
                <span class="badge bg-secondary d-inline-flex align-items-center px-3 py-2">
                    ${escapeHtml(user.name)}
                    <button type="button"
                            class="btn-close btn-close-white ms-2 remove-group-user"
                            data-user-id="${user.id}"
                            style="font-size:10px;"></button>
                </span>
            `;
        });

        $('#selectedGroupUsers').html(html);
    }

    function resetGroupModal() {
        $('#groupName').val('');
        $('#groupDescription').val('');
        $('#groupUserSearch').val('');
        $('#groupUserResults').hide().empty();
        selectedUsers.clear();
        renderSelectedGroupUsers();
        $('#createGroupBtn').prop('disabled', false).html('Crear');
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }

        alert(message);
    }
});
</script>