<?php
$headerTitle = "Amenities";
$headerSubtitle = "See all amenities available in the system.";
$buttonText = "Add New Amenity";
$buttonLink = "add-amenities.php"; // We'll trigger modal instead
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Amenities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/room-type.css">
    <link rel="stylesheet" href="../css/admin/content.css">
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

        <div id="amenityData"></div>

        <!-- Modal for Add/Edit Amenity -->
        <div class="modal" id="amenityModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title"><i class="fas fa-concierge-bell"></i> <span id="modalTitle">Add New
                            Amenity</span></h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="amenityForm">
                        <input type="hidden" name="amenity_id" id="amenityId">
                        <div class="form-group">
                            <label>Amenity Name</label>
                            <input type="text" class="form-control" name="amenity_name" id="amenityName" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" rows="4" name="description" id="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Icon URL</label>
                            <input type="text" class="form-control" name="icon_url" id="iconUrl">
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
        const modal = document.getElementById('amenityModal');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const modalTitle = document.getElementById('modalTitle');

        // Show Add Amenity Modal
        <?php if ($showButton): ?>
            const addBtn = document.querySelector(".btn-primary-form");
        <?php endif; ?>

        function openModal(title = "Add New Amenity") {
            modalTitle.textContent = title;
            modal.style.display = 'flex';
        }

        // Close modal
        const closeModal = () => {
            modal.style.display = 'none';
            document.getElementById('amenityForm').reset();
            document.getElementById('amenityId').value = '';
        };

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

        // Load table
        function loadAmenities(page = 1) {
            fetch('fetch-amenities.php?page=' + page)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('amenityData').innerHTML = html;

                    // Bind pagination
                    document.querySelectorAll('.pagination a').forEach(a => {
                        a.addEventListener('click', e => {
                            e.preventDefault();
                            loadAmenities(a.dataset.page);
                        });
                    });

                    bindEditDelete();
                });
        }

        // Bind edit/delete buttons
        function bindEditDelete() {
            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    fetch('edit-amenity.php?id=' + id)
                        .then(res => res.json())
                        .then(data => {
                            if (data) {
                                document.getElementById('amenityId').value = data.amenity_id;
                                document.getElementById('amenityName').value = data.amenity_name;
                                document.getElementById('description').value = data.description;
                                document.getElementById('iconUrl').value = data.icon_url;
                                openModal("Edit Amenity");
                            }
                        });
                });
            });

            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the amenity!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('delete-amenity.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'id=' + id
                            })
                                .then(res => res.text())
                                .then(resp => {
                                    if (resp.trim() === 'success') {
                                        Swal.fire({ icon: 'success', title: 'Deleted!', showConfirmButton: false, timer: 4000, toast: true, position: 'top-end' });
                                        loadAmenities();
                                    } else {
                                        Swal.fire({ icon: 'error', title: 'Error deleting', text: resp, showConfirmButton: true });
                                    }
                                });
                        }
                    });
                });
            });
        }

        // Save/Add Amenity
        saveBtn.addEventListener('click', e => {
            e.preventDefault();
            const formData = new FormData(document.getElementById('amenityForm'));
            fetch('update-amenity.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.text())
                .then(resp => {
                    if (resp.trim() === 'success') {
                        Swal.fire({ icon: 'success', title: 'Saved!', showConfirmButton: false, timer: 4000, toast: true, position: 'top-end' });
                        closeModal();
                        loadAmenities();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error updating', text: resp, showConfirmButton: true });
                    }
                });
        });

        // Initial load
        loadAmenities();
    </script>
</body>

</html>