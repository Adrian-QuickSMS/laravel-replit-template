@extends('layouts.admin')

@section('title', 'Platform Updates')

@push('styles')
<style>
.pu-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}
.pu-page-header h2 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.pu-page-header p {
    margin: 0.15rem 0 0;
    color: #6c757d;
    font-size: 0.85rem;
}
.btn-admin-primary {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
}
.btn-admin-primary:hover {
    background-color: #152a45;
    border-color: #152a45;
    color: #fff;
}
.pu-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.pu-table {
    width: 100%;
    margin: 0;
}
.pu-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
}
.pu-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.pu-table tbody tr:last-child {
    border-bottom: none;
}
.pu-table tbody td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
    font-size: 0.875rem;
    color: #1e293b;
}
.pu-type-badge {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    border-radius: 1rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pu-type-update      { background: #e0f2fe; color: #0369a1; }
.pu-type-maintenance { background: #fef3c7; color: #92400e; }
.pu-type-feature     { background: #dcfce7; color: #166534; }
.pu-published-badge {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
}
.pu-published      { background: #dcfce7; color: #166534; }
.pu-unpublished    { background: #f1f5f9; color: #64748b; }
.pu-scheduled      { background: #fef3c7; color: #92400e; }
.pu-action-btn {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    padding: 0.25rem 0.55rem;
    font-size: 0.75rem;
    color: #1e3a5f;
    cursor: pointer;
    transition: background 0.12s ease;
}
.pu-action-btn:hover {
    background: #f1f5f9;
}
.pu-action-btn.danger {
    color: #b91c1c;
    border-color: #fecaca;
}
.pu-action-btn.danger:hover {
    background: #fef2f2;
}
.pu-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #64748b;
}
.pu-empty i {
    font-size: 2rem;
    color: #cbd5e1;
    display: block;
    margin-bottom: 0.5rem;
}
.pu-modal-body label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}
.pu-modal-body .form-control,
.pu-modal-body .form-select {
    font-size: 0.875rem;
}
.pu-modal-body textarea.form-control {
    min-height: 140px;
}
.pu-modal-body .form-text {
    font-size: 0.75rem;
}
.pu-toast {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    background: #1e3a5f;
    color: #fff;
    font-size: 0.875rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    z-index: 1080;
    display: none;
}
.pu-toast.error {
    background: #b91c1c;
}
.pu-loading {
    text-align: center;
    padding: 2rem 1rem;
    color: #94a3b8;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="pu-page-header">
        <div>
            <h2>Platform Updates</h2>
            <p>Maintenance notices, new features and general updates shown on the customer Help Centre dashboard.</p>
        </div>
        <button type="button" class="btn btn-admin-primary" id="pu-new-btn">
            <i class="fas fa-plus me-1"></i> New announcement
        </button>
    </div>

    <div class="pu-card">
        <table class="pu-table" id="pu-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Type</th>
                    <th>Title</th>
                    <th style="width: 170px;">Posted at</th>
                    <th style="width: 120px;">Published</th>
                    <th style="width: 100px;">Read count</th>
                    <th style="width: 230px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="pu-tbody">
                <tr><td colspan="6" class="pu-loading"><i class="fas fa-spinner fa-spin me-1"></i> Loading announcements…</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Edit / create modal --}}
<div class="modal fade" id="pu-edit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="pu-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="pu-modal-title">New announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pu-modal-body">
                    <input type="hidden" id="pu-id" value="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="pu-type">Type</label>
                            <select class="form-select" id="pu-type" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="pu-title">Title</label>
                            <input type="text" class="form-control" id="pu-title" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <label for="pu-body">Body</label>
                            <textarea class="form-control" id="pu-body" maxlength="5000" required></textarea>
                            <div class="form-text">Up to 5,000 characters. Plain text or short HTML.</div>
                        </div>
                        <div class="col-md-7">
                            <label for="pu-link-url">Link URL (optional)</label>
                            <input type="url" class="form-control" id="pu-link-url" maxlength="500" placeholder="https://…">
                        </div>
                        <div class="col-md-5">
                            <label for="pu-posted-at">Schedule (posted at)</label>
                            <input type="datetime-local" class="form-control" id="pu-posted-at">
                            <div class="form-text">Leave blank to post immediately.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pu-published" checked>
                                <label class="form-check-label" for="pu-published">
                                    Published &mdash; visible on the customer Help Centre dashboard
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="pu-form-error"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin-primary" id="pu-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Confirm delete modal --}}
<div class="modal fade" id="pu-delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete announcement?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This permanently removes the announcement and every customer&rsquo;s read receipt for it. This cannot be undone.</p>
                <p class="fw-semibold mb-0" id="pu-delete-title"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="pu-delete-confirm-btn">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="pu-toast" id="pu-toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
(function () {
    var endpoints = {
        list:           @json(route('admin.api.platform-updates.index')),
        store:          @json(route('admin.api.platform-updates.store')),
        showTpl:        @json(route('admin.api.platform-updates.show', ['id' => '__ID__'])),
        updateTpl:      @json(route('admin.api.platform-updates.update', ['id' => '__ID__'])),
        toggleTpl:      @json(route('admin.api.platform-updates.toggle-publish', ['id' => '__ID__'])),
        destroyTpl:     @json(route('admin.api.platform-updates.destroy', ['id' => '__ID__'])),
    };
    var csrfToken = @json(csrf_token());

    var tbody          = document.getElementById('pu-tbody');
    var newBtn         = document.getElementById('pu-new-btn');
    var editModalEl    = document.getElementById('pu-edit-modal');
    var deleteModalEl  = document.getElementById('pu-delete-modal');
    var editModal      = new bootstrap.Modal(editModalEl);
    var deleteModal    = new bootstrap.Modal(deleteModalEl);
    var form           = document.getElementById('pu-form');
    var formError      = document.getElementById('pu-form-error');
    var saveBtn        = document.getElementById('pu-save-btn');
    var modalTitle     = document.getElementById('pu-modal-title');
    var fId            = document.getElementById('pu-id');
    var fType          = document.getElementById('pu-type');
    var fTitle         = document.getElementById('pu-title');
    var fBody          = document.getElementById('pu-body');
    var fLink          = document.getElementById('pu-link-url');
    var fPostedAt      = document.getElementById('pu-posted-at');
    var fPublished     = document.getElementById('pu-published');
    var deleteConfirm  = document.getElementById('pu-delete-confirm-btn');
    var deleteTitle    = document.getElementById('pu-delete-title');
    var pendingDelete  = null;
    var toastEl        = document.getElementById('pu-toast');
    var toastTimer;

    function url(tpl, id) { return tpl.replace('__ID__', encodeURIComponent(id)); }

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
        });
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        try {
            var d = new Date(iso);
            if (isNaN(d.getTime())) return '—';
            return d.toLocaleString(undefined, {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        } catch (e) { return iso; }
    }

    function toLocalInputValue(iso) {
        if (!iso) return '';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
             + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function fromLocalInputValue(value) {
        if (!value) return null;
        var d = new Date(value);
        if (isNaN(d.getTime())) return null;
        return d.toISOString();
    }

    function showToast(message, isError) {
        toastEl.textContent = message;
        toastEl.classList.toggle('error', !!isError);
        toastEl.style.display = 'block';
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.style.display = 'none'; }, 3500);
    }

    function jsonFetch(method, target, payload) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        if (payload !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(payload);
        }
        return fetch(target, opts).then(function (res) {
            return res.json().then(function (body) {
                return { ok: res.ok, status: res.status, body: body };
            }).catch(function () {
                return { ok: res.ok, status: res.status, body: null };
            });
        });
    }

    function load() {
        tbody.innerHTML = '<tr><td colspan="6" class="pu-loading"><i class="fas fa-spinner fa-spin me-1"></i> Loading announcements…</td></tr>';
        jsonFetch('GET', endpoints.list).then(function (res) {
            if (!res.ok || !res.body || !res.body.success) {
                tbody.innerHTML = '<tr><td colspan="6" class="pu-empty"><i class="fas fa-exclamation-triangle"></i> Failed to load announcements.</td></tr>';
                return;
            }
            var rows = res.body.data || [];
            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="6" class="pu-empty"><i class="fas fa-bullhorn"></i> No announcements yet. Click "New announcement" to post one.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(renderRow).join('');
        }).catch(function () {
            tbody.innerHTML = '<tr><td colspan="6" class="pu-empty"><i class="fas fa-exclamation-triangle"></i> Failed to load announcements.</td></tr>';
        });
    }

    function renderRow(row) {
        var typeCls = 'pu-type-' + (row.type || 'update');
        var pubCls, pubText;
        if (!row.published) {
            pubCls = 'pu-unpublished'; pubText = 'Unpublished';
        } else if (row.is_scheduled) {
            pubCls = 'pu-scheduled'; pubText = 'Scheduled';
        } else {
            pubCls = 'pu-published'; pubText = 'Live';
        }
        var toggleLabel = row.published ? 'Unpublish' : 'Publish';
        return '<tr data-id="' + escapeHtml(row.id) + '">'
             +   '<td><span class="pu-type-badge ' + typeCls + '">' + escapeHtml(row.type) + '</span></td>'
             +   '<td><div class="fw-semibold">' + escapeHtml(row.title) + '</div>'
             +     (row.link_url ? '<div class="text-muted" style="font-size:0.75rem;">' + escapeHtml(row.link_url) + '</div>' : '')
             +   '</td>'
             +   '<td>' + escapeHtml(fmtDate(row.posted_at)) + '</td>'
             +   '<td><span class="pu-published-badge ' + pubCls + '">' + pubText + '</span></td>'
             +   '<td>' + (row.read_count || 0) + '</td>'
             +   '<td style="text-align:right;">'
             +     '<button type="button" class="pu-action-btn" data-action="edit">Edit</button> '
             +     '<button type="button" class="pu-action-btn" data-action="toggle">' + toggleLabel + '</button> '
             +     '<button type="button" class="pu-action-btn danger" data-action="delete">Delete</button>'
             +   '</td>'
             + '</tr>';
    }

    function resetForm() {
        formError.classList.add('d-none');
        formError.textContent = '';
        fId.value = '';
        fType.value = @json(\App\Models\PlatformUpdate::TYPE_UPDATE);
        fTitle.value = '';
        fBody.value = '';
        fLink.value = '';
        fPostedAt.value = '';
        fPublished.checked = true;
    }

    function openEdit(row) {
        resetForm();
        if (row) {
            modalTitle.textContent = 'Edit announcement';
            fId.value = row.id;
            fType.value = row.type;
            fTitle.value = row.title;
            fBody.value = row.body;
            fLink.value = row.link_url || '';
            fPostedAt.value = toLocalInputValue(row.posted_at);
            fPublished.checked = !!row.published;
        } else {
            modalTitle.textContent = 'New announcement';
        }
        editModal.show();
    }

    newBtn.addEventListener('click', function () { openEdit(null); });

    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var tr = btn.closest('tr');
        var id = tr ? tr.getAttribute('data-id') : null;
        if (!id) return;
        var action = btn.getAttribute('data-action');

        if (action === 'edit') {
            jsonFetch('GET', url(endpoints.showTpl, id)).then(function (res) {
                if (!res.ok || !res.body || !res.body.success) {
                    showToast('Failed to load announcement.', true);
                    return;
                }
                openEdit(res.body.data);
            });
        } else if (action === 'toggle') {
            btn.disabled = true;
            jsonFetch('POST', url(endpoints.toggleTpl, id), {}).then(function (res) {
                btn.disabled = false;
                if (!res.ok || !res.body || !res.body.success) {
                    showToast('Failed to change publish state.', true);
                    return;
                }
                showToast(res.body.data.published ? 'Announcement published.' : 'Announcement unpublished.');
                load();
            }).catch(function () {
                btn.disabled = false;
                showToast('Failed to change publish state.', true);
            });
        } else if (action === 'delete') {
            var titleCell = tr.querySelector('.fw-semibold');
            pendingDelete = id;
            deleteTitle.textContent = titleCell ? titleCell.textContent : '';
            deleteModal.show();
        }
    });

    deleteConfirm.addEventListener('click', function () {
        if (!pendingDelete) return;
        deleteConfirm.disabled = true;
        jsonFetch('DELETE', url(endpoints.destroyTpl, pendingDelete)).then(function (res) {
            deleteConfirm.disabled = false;
            if (!res.ok || !res.body || !res.body.success) {
                showToast('Failed to delete announcement.', true);
                return;
            }
            deleteModal.hide();
            pendingDelete = null;
            showToast('Announcement deleted.');
            load();
        }).catch(function () {
            deleteConfirm.disabled = false;
            showToast('Failed to delete announcement.', true);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        formError.classList.add('d-none');
        formError.textContent = '';

        var payload = {
            type: fType.value,
            title: fTitle.value.trim(),
            body: fBody.value.trim(),
            link_url: fLink.value.trim() || null,
            posted_at: fromLocalInputValue(fPostedAt.value),
            published: fPublished.checked
        };

        var id = fId.value;
        var method = id ? 'PUT' : 'POST';
        var target = id ? url(endpoints.updateTpl, id) : endpoints.store;

        saveBtn.disabled = true;
        jsonFetch(method, target, payload).then(function (res) {
            saveBtn.disabled = false;
            if (res.status === 422 && res.body && res.body.errors) {
                var first = Object.values(res.body.errors)[0];
                formError.textContent = Array.isArray(first) ? first[0] : (res.body.message || 'Validation failed.');
                formError.classList.remove('d-none');
                return;
            }
            if (!res.ok || !res.body || !res.body.success) {
                formError.textContent = (res.body && res.body.error) || 'Failed to save announcement.';
                formError.classList.remove('d-none');
                return;
            }
            editModal.hide();
            showToast(id ? 'Announcement updated.' : 'Announcement created.');
            load();
        }).catch(function () {
            saveBtn.disabled = false;
            formError.textContent = 'Failed to save announcement.';
            formError.classList.remove('d-none');
        });
    });

    load();
})();
</script>
@endpush
