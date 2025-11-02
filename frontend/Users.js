  let users = [];
        let editingUserId = null;

        // DOM Elements
        const userModal = document.getElementById('userModal');
        const userForm = document.getElementById('userForm');
        const addUserBtn = document.getElementById('addUserBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const notification = document.getElementById('notification');
        const submitBtn = document.getElementById('submitBtn');

        // Fetch users from database
        async function fetchUsers() {
            try {
                const response = await fetch('../backend/get_users.php');
                const data = await response.json();
                users = data;
                renderUsers();
                updateUserCount();
            } catch (error) {
                console.error("Error loading users:", error);
                showNotification("Failed to load users", "Failed to connect to server", "error");
            }
        }

        // Render users table
        function renderUsers() {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <h3>No Users Found</h3>
                                <p>Start by adding a new user to the system</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                            <div class="user-details">
                                <div class="user-name">${user.name}</div>
                                <div class="user-email">
                                    <i class="fas fa-envelope"></i>
                                    ${user.email}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="user-age">
                            <i class=""></i>
                            ${user.age} years
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn edit-btn" onclick="editUser(${user.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Update user count
        function updateUserCount() {
            document.getElementById('userCount').textContent = users.length;
        }

        // Show notification
        function showNotification(title, message, type = 'success') {
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            notification.querySelector('i').className = `fas ${icon}`;
            document.getElementById('notificationTitle').textContent = title;
            document.getElementById('notificationMessage').textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        // Open modal
        function openModal(title = 'Add New User', icon = 'fa-user-plus') {
            document.getElementById('modalTitle').innerHTML = `<i class="fas ${icon}"></i> ${title}`;
            userModal.classList.add('active');
        }

        // Close modal
        function closeModal() {
            userModal.classList.remove('active');
            userForm.reset();
            editingUserId = null;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Save User';
        }

        // Edit user
        function editUser(id) {
            const user = users.find(u => parseInt(u.id) === parseInt(id));
            
            if (user) {
                editingUserId = parseInt(id);
                document.getElementById('name').value = user.name;
                document.getElementById('age').value = user.age;
                document.getElementById('email').value = user.email;
                submitBtn.innerHTML = '<i class="fas fa-edit"></i> Update User';
                openModal('Edit User', 'fa-user-edit');
            } else {
                showNotification('Error', 'User not found', 'error');
            }
        }

        // Delete user
        async function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

            const formData = new FormData();
            formData.append('id', id);

            try {
                const res = await fetch('../backend/delete_user.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    users = users.filter(u => parseInt(u.id) !== parseInt(id));
                    renderUsers();
                    updateUserCount();
                    showNotification('Success', data.message, 'success');
                } else {
                    showNotification('Error', data.message, 'error');
                }
            } catch (err) {
                showNotification('Error', 'Failed to delete user', 'error');
                console.error(err);
            }
        }

        // Event Listeners
        addUserBtn.addEventListener('click', () => {
            editingUserId = null;
            openModal();
        });
        
        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Form submit
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const age = parseInt(document.getElementById('age').value);
            const email = document.getElementById('email').value.trim();

            const formData = new FormData();
            formData.append('name', name);
            formData.append('age', age);
            formData.append('email', email);

            let url = '../backend/add_user.php';
            let isEdit = false;

            if (editingUserId) {
                url = '../backend/edit_user.php';
                formData.append('id', editingUserId);
                isEdit = true;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';

            try {
                const res = await fetch(url, { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    if (isEdit) {
                        users = users.map(u => 
                            parseInt(u.id) === parseInt(editingUserId) ? data.user : u
                        );
                        showNotification('Success', 'User updated successfully!', 'success');
                    } else {
                        users.unshift(data.user);
                        showNotification('Success', 'User added successfully!', 'success');
                    }
                    renderUsers();
                    updateUserCount();
                    closeModal();
                } else {
                    showNotification('Error', data.message, 'error');
                }
            } catch (err) {
                showNotification('Error', 'Operation failed', 'error');
                console.error('Error:', err);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = isEdit ? 
                    '<i class="fas fa-edit"></i> Update User' : 
                    '<i class="fas fa-save"></i> Save User';
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === userModal) {
                closeModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // ESC to close modal
            if (e.key === 'Escape' && userModal.classList.contains('active')) {
                closeModal();
            }
        });

        // Load users on page load
        fetchUsers();

        // Auto-refresh every 30 seconds
        setInterval(fetchUsers, 30000);