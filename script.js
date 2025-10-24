console.log("Lost & Found system loaded successfully!");

// Edit modal functions
function editUser(id, name, email, role) {
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = name;
  document.getElementById('editEmail').value = email;
  document.getElementById('editRole').value = role;
  document.getElementById('editModal').style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeModal() {
  document.getElementById('editModal').style.display = 'none';
}

// Toggle between login and register forms
function showForm(formId) {
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');

  // Remove active class from both
  loginForm.classList.remove('active');
  registerForm.classList.remove('active');

  // Show selected
  document.getElementById(formId).classList.add('active');
}
