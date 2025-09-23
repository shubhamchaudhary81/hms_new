<?php
$headerTitle = "Room Type";
$headerSubtitle = "See all room types available in the system.";
$buttonText = "Add New Room Type ";
$buttonLink = "add-room-type.php";
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Types Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <link rel="stylesheet" href="../css/admin/room-type.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            /* Needed if using flex */
            align-items: center;
            /* Needed if using flex */
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header-content.php'; ?>

        <div id="roomTypeData"></div>
        <!-- Edit Room Type Modal -->
        <div class="modal" id="roomTypeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Edit Room Type</h2>
                    <span class="close" style="cursor:pointer">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="roomTypeForm">
                        <input type="hidden" id="roomTypeId" name="room_type_id">

                        <div class="form-group">
                            <label for="roomTypeName">Room Type Name</label>
                            <input type="text" id="roomTypeName" name="room_type_name" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"></textarea>
                        </div>

                        <!-- <div class="form-group">
                            <label for="capacity">Capacity</label>
                            <input type="number" id="capacity" name="capacity" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="basePrice">Base Price ($)</label>
                            <input type="number" id="basePrice" name="base_price" step="0.01" min="0" required>
                        </div> -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const modal = document.getElementById('roomTypeModal');
            const closeBtn = document.querySelector('.close');
            const cancelBtn = document.getElementById('cancelBtn');
            const saveBtn = document.getElementById('saveBtn');

            // Close modal
            const closeModal = () => {
                modal.style.display = 'none';
                document.getElementById('roomTypeForm').reset();
            };
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', e => { e.preventDefault(); closeModal(); });
            window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

            // Toast function
            function showToast(icon, title) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: title,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            // Load table and bind buttons
            function loadRoomTypes(page = 1) {
                fetch('fetch-roomtypes.php?page=' + page)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('roomTypeData').innerHTML = html;

                        document.querySelectorAll('.pagination a').forEach(a => {
                            a.addEventListener('click', e => {
                                e.preventDefault();
                                loadRoomTypes(a.dataset.page);
                            });
                        });

                        bindEditDelete();
                    });
            }

            // Bind edit & delete buttons
            function bindEditDelete() {
                // Edit button
                document.querySelectorAll('.action-btn.edit').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        fetch('edit-room-type.php?id=' + id)
                            .then(res => res.json())
                            .then(data => {
                                if (data) {
                                    document.getElementById('roomTypeId').value = data.room_type_id;
                                    document.getElementById('roomTypeName').value = data.room_type_name;
                                    document.getElementById('description').value = data.description;
                                    modal.style.display = 'flex';
                                }
                            })
                            .catch(err => showToast('error', 'Error fetching data'));
                    });
                });

                // Delete button
                document.querySelectorAll('.action-btn.delete').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('delete-room-type.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'id=' + id
                                })
                                    .then(res => res.text())
                                    .then(resp => {
                                        if (resp.trim() === 'success') {
                                            showToast('success', 'Deleted successfully!');
                                            loadRoomTypes();
                                        } else {
                                            showToast('error', 'Error deleting: ' + resp);
                                        }
                                    });
                            }
                        });
                    });
                });
            }

            // Save changes
            saveBtn.addEventListener('click', e => {
                e.preventDefault();
                const formData = new FormData(document.getElementById('roomTypeForm'));

                fetch('update-room-type.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.text())
                    .then(resp => {
                        if (resp.trim() === 'success') {
                            closeModal();
                            loadRoomTypes();
                            showToast('success', 'Updated successfully!');
                        } else {
                            showToast('error', 'Error updating: ' + resp);
                        }
                    });
            });

            // Initial load
            loadRoomTypes();
            // const modal = document.getElementById('roomTypeModal');
            // const closeBtn = document.querySelector('.close');
            // const cancelBtn = document.getElementById('cancelBtn');
            // const saveBtn = document.getElementById('saveBtn');

            // // Close modal
            // const closeModal = () => {
            //     modal.style.display = 'none';
            //     document.getElementById('roomTypeForm').reset();
            // };

            // closeBtn.addEventListener('click', closeModal);
            // cancelBtn.addEventListener('click', e => { e.preventDefault(); closeModal(); });
            // window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

            // // Load table and bind edit/delete buttons
            // function loadRoomTypes(page = 1) {
            //     fetch('fetch-roomtypes.php?page=' + page)
            //         .then(res => res.text())
            //         .then(html => {
            //             document.getElementById('roomTypeData').innerHTML = html;

            //             document.querySelectorAll('.pagination a').forEach(a => {
            //                 a.addEventListener('click', e => {
            //                     e.preventDefault();
            //                     loadRoomTypes(a.dataset.page);
            //                 });
            //             });

            //             bindEditDelete();
            //         });
            // }

            // // Bind edit & delete buttons
            // function bindEditDelete() {
            //     // Edit button
            //     document.querySelectorAll('.action-btn.edit').forEach(btn => {
            //         btn.addEventListener('click', () => {
            //             const id = btn.dataset.id;
            //             fetch('edit-room-type.php?id=' + id)
            //                 .then(res => res.json())
            //                 .then(data => {
            //                     if (data) {
            //                         document.getElementById('roomTypeId').value = data.room_type_id;
            //                         document.getElementById('roomTypeName').value = data.room_type_name;
            //                         document.getElementById('description').value = data.description;
            //                         modal.style.display = 'flex';
            //                     }
            //                 })
            //                 .catch(err => console.error('Edit fetch error:', err));
            //         });
            //     });

            //     // Delete button
            //     document.querySelectorAll('.action-btn.delete').forEach(btn => {
            //         btn.addEventListener('click', () => {
            //             const id = btn.dataset.id;
            //             if (confirm('Delete this room type?')) {
            //                 fetch('delete-room-type.php', {
            //                     method: 'POST',
            //                     headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            //                     body: 'id=' + id
            //                 })
            //                     .then(res => res.text())
            //                     .then(resp => {
            //                         if (resp.trim() === 'success') {
            //                             alert('Deleted successfully!');
            //                             loadRoomTypes();
            //                         } else {
            //                             alert('Error deleting: ' + resp);
            //                         }
            //                     });
            //             }
            //         });
            //     });
            // }

            // // Save changes
            // saveBtn.addEventListener('click', e => {
            //     e.preventDefault();
            //     const formData = new FormData(document.getElementById('roomTypeForm'));

            //     fetch('update-room-type.php', {
            //         method: 'POST',
            //         body: formData
            //     })
            //         .then(res => res.text())
            //         .then(resp => {
            //             if (resp.trim() === 'success') {
            //                 alert('Updated successfully!');
            //                 closeModal();
            //                 loadRoomTypes();
            //             } else {
            //                 alert('Error updating: ' + resp);
            //             }
            //         });
            // });

            // // Initial load
            // loadRoomTypes();

        </script>
    </div>
</body>

</html>