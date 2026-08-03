function selectUser(email, el) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = ''; // Kosongkan password agar diisi manual
    document.getElementById('passwordInput').focus();
    document.querySelectorAll('.user-btn').forEach(btn => btn.classList.remove('active'));
    el.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const pwd = document.getElementById('passwordInput');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                this.innerHTML = '👀'; 
            } else {
                pwd.type = 'password';
                this.innerHTML = '👁️';
            }
        });
    }
});
