auth.renderNavbar();
        const alertBox = document.getElementById('alert');
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.innerHTML = '';
            try {
                // Create FormData to handle file upload
                const formData = new FormData();
                formData.append('name', document.getElementById('name').value);
                formData.append('email', document.getElementById('email').value);
                formData.append('password', document.getElementById('password').value);
                
                // Add profile picture if selected
                const profilePicInput = document.getElementById('profile_pic');
                if (profilePicInput.files && profilePicInput.files[0]) {
                    formData.append('profile_picture', profilePicInput.files[0]);
                }
                
                await api.post('/auth/register', formData, { auth: false });
                alertBox.innerHTML = '<div class="alert success">Account created. You can now log in.</div>';
                setTimeout(() => window.location.href = 'login.html', 900);
            } catch (err) {
                alertBox.innerHTML = `<div class="alert error">${err.message}</div>`;
            }
        });