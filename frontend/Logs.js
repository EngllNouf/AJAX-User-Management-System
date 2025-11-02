let logs = [];
let filteredLogs = [];
let currentPage = 1;
let totalPages = 1;
const itemsPerPage = 10;
let currentSort = { column: 'timestamp', direction: 'desc' };
let deleteLogId = null;

// ====================== showToast Function ======================
// The showToast() function displays a small temporary notification on the screen
// commonly called a toast It informs the user about an action or event result
// such as success error warning or info

function showToast(type, title, message) {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.innerHTML = `
        <i class="fas ${icons[type]} toast-icon"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000);
}

/* Fetches logs from server
   Updates table pagination stats
   Shows toast notifications on success error */

async function fetchLogs() {
    try {
        const userFilter = document.getElementById('userFilter').value;
        const actionFilter = document.getElementById('actionFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        const params = new URLSearchParams({
            page: currentPage,
            limit: itemsPerPage,
            user: userFilter,
            action: actionFilter,
            date: dateFilter,
            status: statusFilter
        });

        const response = await fetch(`../backend/get_logs.php?${params}`);
        const data = await response.json();

        if (data.success) {
            logs = data.logs;
            filteredLogs = logs;
            totalPages = data.totalPages || Math.ceil(data.total / itemsPerPage);
            renderLogs();
            updatePagination(data.total);
            updateStats();
            showToast('success', 'Logs Updated', 'Activity logs have been refreshed successfully');
        } else {
            showError('Failed to load logs');
            showToast('error', 'Error', 'Failed to load activity logs');
        }
    } catch (error) {
        console.error('Error fetching logs:', error);
        showError('Error loading logs');
        showToast('error', 'Connection Error', 'Unable to fetch logs from server');
    }
}

// Render logs table
function renderLogs() {
    const tbody = document.getElementById('logsTableBody');
    
    if (filteredLogs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Logs Found</h3>
                        <p>No activity logs match your current filters</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredLogs.map(log => `
        <tr>
            <td class="timestamp">
                <i class="far fa-clock"></i>
                ${formatTimestamp(log.timestamp)}
            </td>
            <td>
                <div class="user-cell">
                    <div class="user-avatar">
                        ${log.user.charAt(0).toUpperCase()}
                    </div>
                    <div class="user-info">
                        <div class="user-name">${log.user}</div>
                        <div class="user-role">${log.role || 'User'}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="action-badge action-${log.action}">
                    <i class="fas fa-${getActionIcon(log.action)}"></i>
                    ${log.action}
                </span>
            </td>
            <td>${log.details}</td>
            <td>
                <span class="status-badge status-${log.status}">
                    <span class="status-indicator"></span>
                    ${log.status}
                </span>
            </td>
            <td>
                <button class="btn btn-clear" style="padding: 0.5rem 1rem; font-size: 0.875rem;" onclick="showDeleteModal(${log.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/* Returns the Font Awesome icon name for a given action
   Supported actions: login create update delete.
   If the action is not recognized returns a default bolt icon */

function getActionIcon(action) {
    const icons = {
        'login': 'sign-in-alt',
        'create': 'user-plus',
        'update': 'edit',
        'delete': 'trash'
    };
    return icons[action] || 'bolt';
}

// Format timestamp
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Update statistics
function updateStats() {
    const stats = {
        login: 0,
        create: 0,
        update: 0,
        delete: 0
    };

    logs.forEach(log => {
        if (stats.hasOwnProperty(log.action)) {
            stats[log.action]++;
        }
    });

    document.getElementById('loginCount').textContent = stats.login;
    document.getElementById('createCount').textContent = stats.create;
    document.getElementById('updateCount').textContent = stats.update;
    document.getElementById('deleteCount').textContent = stats.delete;
}

// Update pagination
function updatePagination(total) {
    const start = (currentPage - 1) * itemsPerPage + 1;
    const end = Math.min(currentPage * itemsPerPage, total);
    
    document.getElementById('startRecord').textContent = total > 0 ? start : 0;
    document.getElementById('endRecord').textContent = end;
    document.getElementById('totalRecords').textContent = total;

    renderPaginationButtons();
}

// Render pagination buttons
function renderPaginationButtons() {
    const container = document.getElementById('pageButtons');
    let buttons = '';

    buttons += `
        <button class="page-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
    `;

    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);

    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        buttons += `
            <button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    buttons += `
        <button class="page-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
            Next <i class="fas fa-chevron-right"></i>
        </button>
    `;

    container.innerHTML = buttons;
}

// Change page
function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    fetchLogs();
}

// Sort table
function sortTable(column) {
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'asc';
    }

    filteredLogs.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        if (column === 'timestamp') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        }

        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });

    renderLogs();
    updateSortIcons();
}


// Export logs to CSV
function exportLogs() {
    const csv = [
        ['Timestamp', 'User', 'Action', 'Details', 'Status'],
        ...logs.map(log => [
            formatTimestamp(log.timestamp),
            log.user,
            log.action,
            log.details.replace(/,/g, ';'),
            log.status
        ])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `system_logs_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('success', 'Export Complete', 'Logs have been exported to CSV successfully');
}

// Show error
function showError(message) {
    const tbody = document.getElementById('logsTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    <h3>Error</h3>
                    <p>${message}</p>
                </div>
            </td>
        </tr>
    `;
}

/* ====================== Delete Modal Functions ======================
   showDeleteModal(logId) ----- Opens the delete confirmation modal for a log entry
   closeDeleteModal()      ----- Closes the modal without deleting
   confirmDelete()         --------Sends a request to delete the log entry from the backend

   NOTE:
   - On the Logs page the modal appears and can be confirmed
     but actual deletion from the backend may NOT work here
   - Deletion fully works only on the User Add/Delete pages
*/


function showDeleteModal(logId) {
    deleteLogId = logId;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    deleteLogId = null;
    document.getElementById('deleteModal').classList.remove('active');
}

function confirmDelete() {
    if (!deleteLogId) return;

    fetch('backend/delete_log.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id: deleteLogId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Deleted', 'Log entry has been deleted successfully');
            fetchLogs();
        } else {
            showToast('error', 'Error', data.message || 'Failed to delete log entry');
        }
    })
    .catch(error => {
        showToast('error', 'Error', 'Failed to delete log entry');
        console.error('Delete error:', error);
    });

    closeDeleteModal();
}



// Event listeners
document.getElementById('userFilter').addEventListener('input', () => {
    currentPage = 1;
    fetchLogs();
});

document.getElementById('actionFilter').addEventListener('change', () => {
    currentPage = 1;
    fetchLogs();
});

document.getElementById('dateFilter').addEventListener('change', () => {
    currentPage = 1;
    fetchLogs();
});

document.getElementById('statusFilter').addEventListener('change', () => {
    currentPage = 1;
    fetchLogs();
});

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});

// Auto-refresh every 1 minute
setInterval(fetchLogs, 60000);


// Initial load
fetchLogs();