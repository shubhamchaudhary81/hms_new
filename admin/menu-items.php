<?php
$headerTitle = "Menu Items";
$headerSubtitle = "Manage all food & drink menu items.";
$buttonText = "Add New Menu Item";
$buttonLink = "add-menu-items.php"; // triggers modal
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <link rel="stylesheet" href="../css/admin/room-type.css">
    <link rel="stylesheet" href="../css/admin/modal.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Make modal larger but responsive */
        .modal-content.modal-large {
            max-width: 850px;
            width: 90%;
            padding: 20px;
            border-radius: 12px;
        }

        /* Flex layout for rows with two fields */
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-row .form-group {
            flex: 1;
            /* each takes 50% width */
            min-width: 250px;
            /* ensure small screens don't break */
        }

        /* Image preview */
        #itemImagePreview {
            margin-top: 5px;
            max-height: 80px;
            display: none;
            border-radius: 5px;
        }

        /* Keep buttons consistent */
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
            background: #8c7355;
        }
    </style>
</head>

<body>
    <?php include_once("sidebar.php"); ?>
    <div class="main-content">
        <?php include_once("header-content.php"); ?>

        <div id="menuItemsData"></div>

        <!-- Modal for Add/Edit Menu Item -->
        <div class="modal" id="menuModal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h2 class="modal-title"><i class="fas fa-utensils"></i> <span id="modalTitle">Add New Menu
                            Item</span></h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="menuForm" enctype="multipart/form-data">
                        <input type="hidden" id="menuItemId" name="menu_item_id">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Item Name</label>
                                <input type="text" name="item_name" id="itemName" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Price (NRP)</label>
                                <input type="number" step="0.01" name="price" id="itemPrice" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Type</label>
                                <input type="text" name="item_type" id="itemType" class="form-control"
                                    placeholder="Food, Drink" required>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" id="itemCategory" class="form-control"
                                    placeholder="Breakfast, Snacks">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Availability</label>
                                <select name="is_available" id="itemAvailability" class="form-control">
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Menu Image</label>
                                <input type="file" name="menu_image_file" id="itemImageFile" class="form-control"
                                    accept="image/*">
                                <img id="itemImagePreview" src="" alt="Image Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="item_description" id="itemDescription" class="form-control"
                                rows="3"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button class="btn btn-primary" id="saveBtn">Save Item</button>
                </div>
            </div>
        </div>
    </div>

   <script>
    const modal = document.getElementById('menuModal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');
    const modalTitle = document.getElementById('modalTitle');
    const itemImageFile = document.getElementById('itemImageFile');
    const itemImagePreview = document.getElementById('itemImagePreview');
    const menuItemId = document.getElementById('menuItemId');

    function openModal(title = "Add New Menu Item") {
        modalTitle.textContent = title;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.getElementById('menuForm').reset();
        menuItemId.value = '';
        itemImagePreview.src = '';
        itemImagePreview.style.display = 'none';
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // Preview image when selected
    itemImageFile.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                itemImagePreview.src = e.target.result;
                itemImagePreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            itemImagePreview.src = '';
            itemImagePreview.style.display = 'none';
        }
    });

    // Load table
    function loadMenuItems(page = 1) {
        fetch('fetch-menu-items.php?page=' + page)
            .then(res => res.text())
            .then(html => {
                document.getElementById('menuItemsData').innerHTML = html;
                document.querySelectorAll('.pagination a').forEach(a => {
                    a.addEventListener('click', e => { e.preventDefault(); loadMenuItems(a.dataset.page); });
                });
                bindEditDelete();
            });
    }

    // Bind edit/delete
    function bindEditDelete() {
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                fetch('edit-menu-items.php?id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data) {
                            menuItemId.value = data.menu_item_id;
                            document.getElementById('itemName').value = data.item_name;
                            document.getElementById('itemDescription').value = data.item_description;
                            document.getElementById('itemPrice').value = data.price;
                            document.getElementById('itemType').value = data.item_type;
                            document.getElementById('itemCategory').value = data.category;
                            document.getElementById('itemAvailability').value = data.is_available;

                            // ✅ FIX: Ensure correct path for image
                            if (data.menu_image) {
                                itemImagePreview.src = data.menu_image; 
                                itemImagePreview.style.display = 'block';
                            } else {
                                itemImagePreview.src = '';
                                itemImagePreview.style.display = 'none';
                            }

                            openModal("Edit Menu Item");
                        }
                    });
            });
        });

        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will delete the menu item!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('delete-menu-items.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id=' + id
                        }).then(res => res.text()).then(resp => {
                            if (resp.trim() === 'success') {
                                Swal.fire({ icon: 'success', title: 'Menu Item Deleted!', showConfirmButton: false, timer: 3000, toast: true, position: 'top-end' });
                                loadMenuItems();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error deleting', text: resp, showConfirmButton: true });
                            }
                        });
                    }
                });
            });
        });
    }

    // Save/Add Menu Item
    saveBtn.addEventListener('click', e => {
        e.preventDefault();
        const formData = new FormData(document.getElementById('menuForm'));
        fetch('update-menu-items.php', {
            method: 'POST',
            body: formData
        }).then(res => res.text()).then(resp => {
            if (resp.trim() === 'success') {
                Swal.fire({ icon: 'success', title: 'Update Saved!', showConfirmButton: false, timer: 3000, toast: true, position: 'top-end' });
                closeModal();
                loadMenuItems();
            } else {
                Swal.fire({ icon: 'error', title: 'Error updating', text: resp, showConfirmButton: true });
            }
        });
    });

    // Initial load
    loadMenuItems();
</script>
</body>

</html>