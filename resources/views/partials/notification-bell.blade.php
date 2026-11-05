<div class="dropdown" id="notification-bell">
    <a href="#" class="topbar-action text-decoration-none position-relative" data-bs-toggle="dropdown" aria-expanded="false">
        <i data-feather="bell"></i>
        <span id="notif-badge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle d-none" style="font-size: 10px; padding: 4px 6px; transform: translate(-30%, -20%) !important;">
            0
        </span>
    </a>

    <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow" style="width: 340px; max-height: 400px; overflow-y: auto; border-radius: 16px;">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light" style="border-radius: 16px 16px 0 0;">
            <strong style="color: #0F172A;">Notifikasi</strong>
            <button type="button" id="notif-mark-all" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: #4F46E5;">
                Tandai semua dibaca
            </button>
        </div>
        <div id="notif-list">
            <div class="text-center text-muted py-4">Memuat...</div>
        </div>
    </div>
</div>

<script>
(function () {
    const badge = document.getElementById('notif-badge');
    const list = document.getElementById('notif-list');
    const markAllBtn = document.getElementById('notif-mark-all');

    function renderNotifications(data) {
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        if (data.notifications.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3">Belum ada notifikasi.</div>';
            return;
        }

        list.innerHTML = data.notifications.map(function (n) {
            const bg = n.read ? '' : 'bg-light';
            return `
                <div class="px-3 py-2 border-bottom ${bg}" style="cursor:pointer" onclick="markNotifRead('${n.id}')">
                    <div class="small">${n.message}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">${n.created_at}</div>
                </div>
            `;
        }).join('');
    }

    function fetchNotifications() {
        fetch('{{ route('notifications.index') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(res => res.json())
            .then(renderNotifications)
            .catch(() => {});
    }

    window.markNotifRead = function (id) {
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(fetchNotifications);
    };

    markAllBtn.addEventListener('click', function (e) {
        e.preventDefault();
        fetch('{{ route('notifications.markAllRead') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        }).then(fetchNotifications);
    });

    fetchNotifications();
    setInterval(fetchNotifications, 30000); // polling tiap 30 detik
})();
</script>
