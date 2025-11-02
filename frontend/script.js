   document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const loginForm = document.getElementById('loginForm');
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const loginBtn = document.getElementById('loginBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const rememberMe = document.getElementById('rememberMe');

            // Password toggle functionality
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

     
            // Show an error message temporarily
            function showError(message) {
                errorMessage.textContent = message;
                errorAlert.classList.add('show');
                successAlert.classList.remove('show');
                setTimeout(() => {
                    errorAlert.classList.remove('show');
                }, 5000);
            }

            // Show a success message
            function showSuccess(message) {
                successMessage.textContent = message;
                successAlert.classList.add('show');
                errorAlert.classList.remove('show');
            }
             // ****************************** Loading State Management ****************************** //
            function startLoading() {
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
                loadingOverlay.classList.add('active');
            }
            
             
            function stopLoading() {
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
                loadingOverlay.classList.remove('active');
            }
              // ****************************** Loading State Management ****************************** //

            // Input validation
            function validateInput(input) {
                const group = input.closest('.input-group');
                const value = input.value.trim();

                if (input.id === 'username') {
                    if (value.length < 3) {
                        group.classList.add('error'); 
                        return false;
                    } else {
                        group.classList.remove('error');
                        return true;
                    }
                }

                if (input.id === 'password') {
                    if (value.length < 6) {
                        group.classList.add('error');
                        return false;
                    } else {
                        group.classList.remove('error');
                        return true;
                    }
                }

                return true;
            }

            // Real-time validation
            usernameInput.addEventListener('blur', function() {
                validateInput(this);
            });

            passwordInput.addEventListener('blur', function() {
                validateInput(this);
            });

            usernameInput.addEventListener('input', function() {
                if (this.value.length >= 3) {
                    document.getElementById('usernameGroup').classList.remove('error');
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value.length >= 6) {
                    document.getElementById('passwordGroup').classList.remove('error');
                }
            });

            // Form submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const username = usernameInput.value.trim();
                const password = passwordInput.value.trim();

                // Validate inputs
                const isUsernameValid = validateInput(usernameInput);
                const isPasswordValid = validateInput(passwordInput);

                if (!isUsernameValid || !isPasswordValid) {
                    showError('Please fix the errors before submitting');
                    return;
                }

                if (!username) {
                    showError('Please enter your username');
                    document.getElementById('usernameGroup').classList.add('error');
                    return;
                }

                if (!password) {
                    showError('Please enter your password');
                    document.getElementById('passwordGroup').classList.add('error');
                    return;
                }

                startLoading();

                // AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "backend/login.php", true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    stopLoading();
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                showSuccess(response.message || 'Login successful! Redirecting...');
                                
                            
                                setTimeout(() => {
                                    window.location.href = response.redirect || 'dashboard.html';
                                }, 1500);
                            } else {
                                showError(response.message || 'Invalid credentials. Please try again.');
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            // Fallback for non-JSON responses
                            if (xhr.responseText.includes('Welcome') || xhr.responseText.includes('success')) {
                                showSuccess('Login successful! Redirecting...');
                                
                                if (rememberMe.checked) {
                                    localStorage.setItem('username', username);
                                }
                                
                                setTimeout(() => {
                                    window.location.href = 'dashboard.html';
                                }, 1500);
                            } else {
                                showError(xhr.responseText || 'Invalid credentials. Please try again.');
                            }
                        }
                    } else {
                        showError('Server error! Please try again later.');
                    }
                };

                xhr.onerror = function() {
                    stopLoading();
                    showError('Connection error! Please check your internet connection.');
                };

                xhr.ontimeout = function() {
                    stopLoading();
                    showError('Request timed out! Please try again.');
                };

                xhr.timeout = 15000; // 15 seconds

                // Prepare form data
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                let formData = `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`;
                
                if (csrfToken) {
                    formData += `&csrf_token=${encodeURIComponent(csrfToken)}`;
                }

                xhr.send(formData);
            });

            // Enter key support
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !loginBtn.disabled) {
                    loginForm.dispatchEvent(new Event('submit'));
                }
            });

            // Social login buttons (placeholder functionality)
            document.querySelectorAll('.social-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const provider = this.classList.contains('google') ? 'Google' : 'Facebook';
                    showError(`${provider} login is not implemented yet`);
                });
            });

            // Auto-focus on username field
            usernameInput.focus();
        });