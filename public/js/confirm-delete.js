// Selecionando elementos
const deleteButton = document.getElementById('deleteButton');
const confirmationPopup = document.getElementById('confirmationPopup');
const cancelDelete = document.getElementById('cancelDelete');
const confirmDelete = document.getElementById('confirmDelete');
const deleteForm = document.getElementById('deleteForm');

// Quando o botão "Excluir" for clicado, exibe o popup
deleteButton.addEventListener('click', function() {
    confirmationPopup.style.display = 'flex';
});

// Quando "Cancelar" for clicado, fecha o popup
cancelDelete.addEventListener('click', function() {
    confirmationPopup.style.display = 'none';
});

// Quando "Excluir" for clicado, envia o formulário
confirmDelete.addEventListener('click', function() {
    deleteForm.submit(); // Envia o formulário de exclusão
    confirmationPopup.style.display = 'none'; // Fecha o popup
});
