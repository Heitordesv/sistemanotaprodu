<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
.table-responsive {
    overflow: visible !important;
    padding-bottom: 50px;
}

.dropdown-menu {
    z-index: 1065 !important;
    border: none !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    border-radius: 12px !important;
    padding: 8px !important;
    min-width: 180px !important;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-item {
    border-radius: 8px !important;
    padding: 8px 12px !important;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    color: #566a7f;
}

.dropdown-item:hover {
    background-color: #f3f4f6 !important;
    transform: translateX(4px);
}

.dropdown-item i {
    font-size: 1.1rem;
    vertical-align: middle;
}

.btn-group .btn {
    transition: all 0.2s ease-in-out;
    border-width: 1.5px;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.show-referencia {
    transition: color 0.2s ease;
    cursor: pointer;
}

.show-referencia:hover {
    color: #696cff !important;
}

.table-light-danger {
    background-color: rgba(255, 62, 29, 0.05) !important;
    border-left: 4px solid #ff3e1d !important;
}

.cursor-pointer {
    cursor: pointer !important;
}
</style>