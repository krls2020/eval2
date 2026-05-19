<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Task Board</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }
        header {
            padding: 18px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid #1e293b;
            position: relative;
        }
        header h1 { margin: 0; font-size: 20px; font-weight: 600; letter-spacing: .2px; }
        header .add-list { display: flex; gap: 8px; }
        header .spacer { flex: 1; }
        header .search {
            position: relative;
            width: min(420px, 40vw);
        }
        header .search input { width: 100%; }
        input, textarea {
            font: inherit;
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 7px 10px;
            outline: none;
        }
        input:focus, textarea:focus { border-color: #6366f1; }
        button {
            font: inherit;
            background: #6366f1;
            color: white;
            border: 0;
            border-radius: 6px;
            padding: 7px 14px;
            cursor: pointer;
        }
        button:hover { background: #4f46e5; }
        button.ghost {
            background: transparent;
            color: #94a3b8;
            padding: 4px 8px;
            font-size: 13px;
        }
        button.ghost:hover { color: #e2e8f0; background: #1e293b; }

        .board {
            display: flex;
            gap: 16px;
            padding: 24px 28px;
            overflow-x: auto;
            align-items: flex-start;
            min-height: calc(100vh - 76px);
        }
        .list {
            background: #1e293b;
            border-radius: 10px;
            width: 300px;
            flex: 0 0 300px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: calc(100vh - 130px);
        }
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .list-header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #cbd5e1;
        }
        .list-header .count {
            color: #64748b;
            font-size: 12px;
            margin-left: 6px;
        }
        .tasks {
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            min-height: 10px;
            padding: 2px;
        }
        .task {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            cursor: grab;
            user-select: none;
            position: relative;
        }
        .task.flash { box-shadow: 0 0 0 2px #f59e0b; }
        .task:active { cursor: grabbing; }
        .task .title { font-weight: 500; }
        .task .desc {
            margin-top: 6px;
            font-size: 12px;
            color: #94a3b8;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .task .attach-badge {
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #94a3b8;
            font-size: 11px;
        }
        .task .actions {
            display: none;
            position: absolute;
            top: 6px;
            right: 6px;
            gap: 4px;
        }
        .task:hover .actions { display: flex; }
        .add-task {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 4px;
        }
        .add-task input { font-size: 13px; }
        .sortable-ghost { opacity: 0.4; }
        .sortable-chosen { box-shadow: 0 8px 16px rgba(0,0,0,0.4); }
        .empty {
            text-align: center;
            color: #475569;
            font-size: 13px;
            padding: 8px;
        }
        dialog {
            background: #1e293b;
            color: #e2e8f0;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 20px;
            width: min(480px, 90vw);
        }
        dialog::backdrop { background: rgba(0,0,0,0.5); }
        dialog h3 { margin: 0 0 12px; }
        dialog h4 { margin: 16px 0 8px; font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
        dialog .row { display: flex; flex-direction: column; gap: 8px; }
        dialog .row textarea { min-height: 100px; resize: vertical; }
        dialog .footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
        dialog .footer button.cancel { background: #334155; }

        .attach-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 180px;
            overflow-y: auto;
        }
        .attach-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            font-size: 13px;
        }
        .attach-row a { color: #93c5fd; text-decoration: none; flex: 1; word-break: break-all; }
        .attach-row a:hover { text-decoration: underline; }
        .attach-row .size { color: #64748b; font-size: 11px; }
        .attach-empty { color: #475569; font-size: 12px; padding: 4px 0; }

        .upload-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 6px;
        }
        .upload-row input[type="file"] { flex: 1; font-size: 12px; }
        .upload-error { color: #fca5a5; font-size: 12px; margin-top: 4px; }

        .search-results {
            position: absolute;
            top: 56px;
            right: 28px;
            width: min(420px, 40vw);
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            box-shadow: 0 16px 32px rgba(0,0,0,0.45);
            max-height: 60vh;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }
        .search-results.open { display: block; }
        .search-group { padding: 8px 12px; border-bottom: 1px solid #0f172a; }
        .search-group:last-child { border-bottom: 0; }
        .search-group h5 {
            margin: 0 0 6px;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .search-item {
            padding: 6px 8px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .search-item:hover { background: #0f172a; }
        .search-item .meta { color: #64748b; font-size: 11px; margin-top: 2px; }
        .search-empty { padding: 12px; color: #64748b; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
<header>
    <h1>Team Task Board</h1>
    <div class="search">
        <input id="search-input" type="search" placeholder="Search lists and tasks..." autocomplete="off">
    </div>
    <div class="spacer"></div>
    <form id="add-list-form" class="add-list">
        <input id="new-list-name" type="text" placeholder="New column name" maxlength="120" required>
        <button type="submit">Add column</button>
    </form>
    <div id="search-results" class="search-results"></div>
</header>

<div id="board" class="board">
    @foreach($lists as $list)
        <section class="list" data-list-id="{{ $list->id }}">
            <div class="list-header">
                <h2>{{ $list->name }} <span class="count">({{ $list->tasks->count() }})</span></h2>
                <button class="ghost" data-action="delete-list" title="Delete column">×</button>
            </div>
            <div class="tasks" data-list-id="{{ $list->id }}">
                @foreach($list->tasks as $task)
                    <article class="task" data-task-id="{{ $task->id }}" data-attachments="{{ $task->attachments->count() }}">
                        <div class="actions">
                            <button class="ghost" data-action="edit-task" title="Edit">✎</button>
                            <button class="ghost" data-action="delete-task" title="Delete">×</button>
                        </div>
                        <div class="title">{{ $task->title }}</div>
                        @if($task->description)
                            <div class="desc">{{ $task->description }}</div>
                        @endif
                        @if($task->attachments->count() > 0)
                            <div class="attach-badge">📎 {{ $task->attachments->count() }}</div>
                        @endif
                    </article>
                @endforeach
            </div>
            <form class="add-task" data-list-id="{{ $list->id }}">
                <input type="text" name="title" placeholder="+ Add a task" maxlength="200" required>
            </form>
        </section>
    @endforeach
</div>

<dialog id="edit-dialog">
    <h3>Edit task</h3>
    <form id="edit-form" class="row">
        <input id="edit-title" type="text" name="title" maxlength="200" required>
        <textarea id="edit-description" name="description" placeholder="Description (optional)"></textarea>
        <div class="footer">
            <button type="button" class="cancel" data-close>Cancel</button>
            <button type="submit">Save</button>
        </div>
    </form>

    <h4>Attachments</h4>
    <div id="attach-list" class="attach-list"></div>
    <form id="upload-form" class="upload-row" enctype="multipart/form-data">
        <input id="upload-file" type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
    <div id="upload-error" class="upload-error" hidden></div>
</dialog>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

async function api(method, url, body) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}

async function uploadFile(url, file) {
    const formData = new FormData();
    formData.append('file', file);
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}

function snapshot() {
    return [...document.querySelectorAll('.list')].map(listEl => ({
        id: parseInt(listEl.dataset.listId),
        task_ids: [...listEl.querySelectorAll('.task')].map(t => parseInt(t.dataset.taskId)),
    }));
}

async function persistOrder() {
    await api('POST', '/board/reorder', { lists: snapshot() });
    updateCounts();
}

function updateCounts() {
    document.querySelectorAll('.list').forEach(listEl => {
        const n = listEl.querySelectorAll('.task').length;
        listEl.querySelector('.count').textContent = `(${n})`;
    });
}

function wireTaskContainer(el) {
    new Sortable(el, {
        group: 'tasks',
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: persistOrder,
    });
}

document.querySelectorAll('.tasks').forEach(wireTaskContainer);

new Sortable(document.getElementById('board'), {
    animation: 150,
    handle: '.list-header',
    draggable: '.list',
    onEnd: persistOrder,
});

// Add column
document.getElementById('add-list-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('new-list-name');
    const name = input.value.trim();
    if (!name) return;
    const list = await api('POST', '/lists', { name });
    const section = document.createElement('section');
    section.className = 'list';
    section.dataset.listId = list.id;
    section.innerHTML = `
        <div class="list-header">
            <h2>${escapeHtml(list.name)} <span class="count">(0)</span></h2>
            <button class="ghost" data-action="delete-list" title="Delete column">×</button>
        </div>
        <div class="tasks" data-list-id="${list.id}"></div>
        <form class="add-task" data-list-id="${list.id}">
            <input type="text" name="title" placeholder="+ Add a task" maxlength="200" required>
        </form>
    `;
    document.getElementById('board').appendChild(section);
    wireTaskContainer(section.querySelector('.tasks'));
    input.value = '';
    await persistOrder();
});

// Add task / delete list / edit & delete task — event delegation
document.getElementById('board').addEventListener('submit', async (e) => {
    if (!e.target.matches('.add-task')) return;
    e.preventDefault();
    const form = e.target;
    const listId = parseInt(form.dataset.listId);
    const titleInput = form.querySelector('input[name=title]');
    const title = titleInput.value.trim();
    if (!title) return;
    const task = await api('POST', '/tasks', { board_list_id: listId, title });
    const listEl = form.closest('.list');
    const tasksEl = listEl.querySelector('.tasks');
    tasksEl.appendChild(renderTask(task));
    titleInput.value = '';
    updateCounts();
});

document.getElementById('board').addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;

    if (action === 'delete-list') {
        const listEl = btn.closest('.list');
        if (!confirm(`Delete this column and all its tasks?`)) return;
        await api('DELETE', `/lists/${listEl.dataset.listId}`);
        listEl.remove();
        return;
    }
    if (action === 'delete-task') {
        const taskEl = btn.closest('.task');
        await api('DELETE', `/tasks/${taskEl.dataset.taskId}`);
        taskEl.remove();
        updateCounts();
        return;
    }
    if (action === 'edit-task') {
        const taskEl = btn.closest('.task');
        openEdit(taskEl);
        return;
    }
});

// Edit dialog + attachments
const dialog = document.getElementById('edit-dialog');
const editForm = document.getElementById('edit-form');
const attachListEl = document.getElementById('attach-list');
const uploadForm = document.getElementById('upload-form');
const uploadFileInput = document.getElementById('upload-file');
const uploadError = document.getElementById('upload-error');
let editingTaskEl = null;

async function openEdit(taskEl) {
    editingTaskEl = taskEl;
    document.getElementById('edit-title').value = taskEl.querySelector('.title').textContent;
    document.getElementById('edit-description').value = taskEl.querySelector('.desc')?.textContent ?? '';
    uploadError.hidden = true;
    uploadFileInput.value = '';
    attachListEl.innerHTML = '<div class="attach-empty">Loading…</div>';
    dialog.showModal();
    try {
        const items = await api('GET', `/tasks/${taskEl.dataset.taskId}/attachments`);
        renderAttachments(items);
    } catch (err) {
        attachListEl.innerHTML = '<div class="attach-empty">Failed to load attachments.</div>';
    }
}

function renderAttachments(items) {
    if (!items.length) {
        attachListEl.innerHTML = '<div class="attach-empty">No attachments yet.</div>';
        updateAttachBadge(0);
        return;
    }
    attachListEl.innerHTML = '';
    items.forEach(a => attachListEl.appendChild(renderAttachmentRow(a)));
    updateAttachBadge(items.length);
}

function renderAttachmentRow(a) {
    const row = document.createElement('div');
    row.className = 'attach-row';
    row.dataset.attachmentId = a.id;
    const link = document.createElement('a');
    link.href = a.download_url;
    link.textContent = a.original_name;
    link.target = '_blank';
    link.rel = 'noopener';
    const size = document.createElement('span');
    size.className = 'size';
    size.textContent = formatBytes(a.size);
    const del = document.createElement('button');
    del.className = 'ghost';
    del.type = 'button';
    del.title = 'Delete';
    del.textContent = '×';
    del.addEventListener('click', async () => {
        if (!confirm('Delete this attachment?')) return;
        await api('DELETE', `/attachments/${a.id}`);
        row.remove();
        const remaining = attachListEl.querySelectorAll('.attach-row').length;
        if (remaining === 0) {
            attachListEl.innerHTML = '<div class="attach-empty">No attachments yet.</div>';
        }
        updateAttachBadge(remaining);
    });
    row.append(link, size, del);
    return row;
}

function updateAttachBadge(count) {
    if (!editingTaskEl) return;
    editingTaskEl.dataset.attachments = String(count);
    let badge = editingTaskEl.querySelector('.attach-badge');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'attach-badge';
            editingTaskEl.appendChild(badge);
        }
        badge.textContent = `📎 ${count}`;
    } else if (badge) {
        badge.remove();
    }
}

uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!editingTaskEl) return;
    const file = uploadFileInput.files[0];
    if (!file) return;
    uploadError.hidden = true;
    try {
        const attachment = await uploadFile(`/tasks/${editingTaskEl.dataset.taskId}/attachments`, file);
        const empty = attachListEl.querySelector('.attach-empty');
        if (empty) empty.remove();
        attachListEl.prepend(renderAttachmentRow(attachment));
        uploadFileInput.value = '';
        updateAttachBadge(attachListEl.querySelectorAll('.attach-row').length);
    } catch (err) {
        uploadError.hidden = false;
        uploadError.textContent = 'Upload failed: ' + (err.message || 'unknown error');
    }
});

dialog.querySelector('[data-close]').addEventListener('click', () => dialog.close());

editForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const title = document.getElementById('edit-title').value.trim();
    const description = document.getElementById('edit-description').value;
    const task = await api('PATCH', `/tasks/${editingTaskEl.dataset.taskId}`, { title, description });
    editingTaskEl.querySelector('.title').textContent = task.title;
    let descEl = editingTaskEl.querySelector('.desc');
    if (task.description) {
        if (!descEl) {
            descEl = document.createElement('div');
            descEl.className = 'desc';
            const badge = editingTaskEl.querySelector('.attach-badge');
            if (badge) editingTaskEl.insertBefore(descEl, badge);
            else editingTaskEl.appendChild(descEl);
        }
        descEl.textContent = task.description;
    } else if (descEl) {
        descEl.remove();
    }
    dialog.close();
});

function renderTask(task) {
    const el = document.createElement('article');
    el.className = 'task';
    el.dataset.taskId = task.id;
    el.dataset.attachments = '0';
    el.innerHTML = `
        <div class="actions">
            <button class="ghost" data-action="edit-task" title="Edit">✎</button>
            <button class="ghost" data-action="delete-task" title="Delete">×</button>
        </div>
        <div class="title"></div>
    `;
    el.querySelector('.title').textContent = task.title;
    if (task.description) {
        const d = document.createElement('div');
        d.className = 'desc';
        d.textContent = task.description;
        el.appendChild(d);
    }
    return el;
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0; let n = bytes;
    while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
    return `${n.toFixed(n < 10 && i > 0 ? 1 : 0)} ${units[i]}`;
}

function escapeHtml(s) {
    return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// Search
const searchInput = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
let searchTimer = null;

searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim();
    clearTimeout(searchTimer);
    if (!q) {
        searchResults.classList.remove('open');
        searchResults.innerHTML = '';
        return;
    }
    searchTimer = setTimeout(() => runSearch(q), 200);
});

searchInput.addEventListener('focus', () => {
    if (searchResults.innerHTML) searchResults.classList.add('open');
});

document.addEventListener('click', (e) => {
    if (!searchResults.contains(e.target) && e.target !== searchInput) {
        searchResults.classList.remove('open');
    }
});

async function runSearch(q) {
    try {
        const res = await fetch(`/search?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        renderSearch(data);
    } catch (err) {
        searchResults.innerHTML = '<div class="search-empty">Search failed.</div>';
        searchResults.classList.add('open');
    }
}

function renderSearch(data) {
    const lists = data.lists || [];
    const tasks = data.tasks || [];
    if (!lists.length && !tasks.length) {
        searchResults.innerHTML = '<div class="search-empty">No matches.</div>';
        searchResults.classList.add('open');
        return;
    }
    let html = '';
    if (lists.length) {
        html += '<div class="search-group"><h5>Columns</h5>';
        lists.forEach(l => {
            html += `<div class="search-item" data-jump-list="${l.id}">${escapeHtml(l.name)}</div>`;
        });
        html += '</div>';
    }
    if (tasks.length) {
        html += '<div class="search-group"><h5>Tasks</h5>';
        tasks.forEach(t => {
            html += `<div class="search-item" data-jump-task="${t.id}">
                <div>${escapeHtml(t.title)}</div>
                <div class="meta">${escapeHtml(t.list_name || '')}</div>
            </div>`;
        });
        html += '</div>';
    }
    searchResults.innerHTML = html;
    searchResults.classList.add('open');
}

searchResults.addEventListener('click', (e) => {
    const taskItem = e.target.closest('[data-jump-task]');
    if (taskItem) {
        flashAndScroll(document.querySelector(`.task[data-task-id="${taskItem.dataset.jumpTask}"]`));
        searchResults.classList.remove('open');
        searchInput.value = '';
        return;
    }
    const listItem = e.target.closest('[data-jump-list]');
    if (listItem) {
        flashAndScroll(document.querySelector(`.list[data-list-id="${listItem.dataset.jumpList}"]`));
        searchResults.classList.remove('open');
        searchInput.value = '';
    }
});

function flashAndScroll(el) {
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    el.classList.add('flash');
    setTimeout(() => el.classList.remove('flash'), 1600);
}
</script>
</body>
</html>
