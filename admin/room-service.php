<?php
$headerTitle = "Room Services";
$headerSubtitle = "Manage all room services in the system.";
$buttonText = "Add New Room Service";
$buttonLink = "add-roomservices.php"; // triggers modal
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Room Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <link rel="stylesheet" href="../css/admin/room-type.css">
    <link rel="stylesheet" href="../css/admin/modal.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .modal-footer .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .modal-footer .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-footer .btn-secondary:hover {
            background: #d1d5db;
        }

        .modal-footer .btn-primary {
            background: #A98F6D;
            color: #fff;
        }

        .modal-footer .btn-primary:hover {
            background: #A98F6D;
        }
    </style>
</head>

<body>
    <?php include_once("sidebar.php"); ?>
    <div class="main-content">
        <?php include_once("header-content.php"); ?>

        <div id="roomServiceData"></div>

        <!-- Modal for Add/Edit Room Service -->
        <div class="modal" id="serviceModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><i class="fas fa-concierge-bell"></i> <span id="modalTitle">Add New
                            Service</span></h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="serviceForm">
                        <input type="hidden" id="serviceId" name="room_service_id">
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" name="service_name" id="serviceName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Price (NRP)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Availability</label>
                            <select name="availability_status" id="availability" class="form-control">
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn">Save Service</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('serviceModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const modalTitle = document.getElementById('modalTitle');

        function openModal(title = "Add New Service") {
            modalTitle.textContent = title;
            modal.style.display = 'flex';
        }

        const closeModal = () => {
            modal.style.display = 'none';
            document.getElementById('serviceForm').reset();
            document.getElementById('serviceId').value = '';
        };

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

        // Load table
        function loadRoomServices(page = 1) {
            fetch('fetch-room-service.php?page=' + page)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('roomServiceData').innerHTML = html;
                    document.querySelectorAll('.pagination a').forEach(a => {
                        a.addEventListener('click', e => { e.preventDefault(); loadRoomServices(a.dataset.page); });
                    });
                    bindEditDelete();
                });
        }

        // Bind edit/delete buttons
        function bindEditDelete() {
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    fetch('edit-room-service.php?id=' + id)
                        .then(res => res.json())
                        .then(data => {
                            if (data) {
                                document.getElementById('serviceId').value = data.room_service_id;
                                document.getElementById('serviceName').value = data.service_name;
                                document.getElementById('description').value = data.description;
                                document.getElementById('price').value = data.price;
                                document.getElementById('availability').value = data.availability_status;
                                openModal("Edit Service");
                            }
                        });
                });
            });

            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will delete the service!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('delete-room-service.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'id=' + id
                            })
                                .then(res => res.text())
                                .then(resp => {
                                    if (resp.trim() === 'success') {
                                        Swal.fire({ icon: 'success', title: 'Room-Service Deleted!', showConfirmButton: false, timer: 3000, toast: true, position: 'top-end' });
                                        loadRoomServices();
                                    } else {
                                        Swal.fire({ icon: 'error', title: 'Error deleting', text: resp, showConfirmButton: true });
                                    }
                                });
                        }
                    });
                });
            });
        }

        // Save/Add Service
        saveBtn.addEventListener('click', e => {
            e.preventDefault();
            const formData = new FormData(document.getElementById('serviceForm'));
            fetch('update-room-service.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.text())
                .then(resp => {
                    if (resp.trim() === 'success') {
                        Swal.fire({ icon: 'success', title: 'Update Changed and Saved.', showConfirmButton: false, timer: 3000, toast: true, position: 'top-end' });
                        closeModal();
                        loadRoomServices();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error updating', text: resp, showConfirmButton: true });
                    }
                });
        });

        // Initial load
        loadRoomServices();
    </script>
</body>

</html>