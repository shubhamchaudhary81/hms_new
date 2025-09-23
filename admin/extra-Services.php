<?php
$headerTitle = "Extra Services";
$headerSubtitle = "See all extra services available in the system.";
$buttonText = "Add New Extra Service";
$buttonLink = "add-extra-service.php";
$showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Extra Services</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin/room-type.css">
<link rel="stylesheet" href="../css/admin/content.css">      
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<?php include_once("sidebar.php"); ?>
<div class="main-content">
    <?php include_once("header-content.php"); ?>

    <div id="extraServiceData"></div>

</div>

<!-- Modal -->
<div class="modal" id="serviceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-concierge-bell"></i> <span id="modalTitle">Add New Service</span></h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="serviceForm">
                <input type="hidden" id="serviceId" name="service_id">
                <div class="form-group">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" name="service_name" placeholder="e.g. Airport Pickup" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="4" name="description" placeholder="Service details" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (in NRP)</label>
                    <input type="number" step="0.01" class="form-control" name="price" placeholder="e.g. 50.00" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-form btn-outline-form" id="cancelBtn">Cancel</button>
            <button class="btn-form btn-primary-form" id="saveBtn">Save Service</button>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('serviceModal');
const closeBtn = document.querySelector('.close');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');
const modalTitle = document.getElementById('modalTitle');

// Open modal for Add/Edit
function openModal(title) {
    modalTitle.textContent = title;
    modal.style.display = 'flex';
}

// Close modal
const closeModal = () => {
    modal.style.display = 'none';
    document.getElementById('serviceForm').reset();
};
closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', e => { e.preventDefault(); closeModal(); });
window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

// Toast
function showToast(icon, title) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
}

// Load extra services from database
function loadExtraServices(page = 1) {
    fetch('fetch-extra-services.php?page=' + page)
    .then(res => res.text())
    .then(html => {
        document.getElementById('extraServiceData').innerHTML = html;

        document.querySelectorAll('.pagination a').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                loadExtraServices(a.dataset.page);
            });
        });

        bindEditDelete();
    });
}

// Bind edit & delete buttons
function bindEditDelete() {
    // Edit
    document.querySelectorAll('.action-btn.edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch('edit-extra-service.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if(data){
                    document.getElementById('serviceId').value = data.service_id;
                    document.querySelector('[name="service_name"]').value = data.service_name;
                    document.querySelector('[name="description"]').value = data.description;
                    document.querySelector('[name="price"]').value = data.price;
                    openModal('Edit Service');
                }
            });
        });
    });

    // Delete
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This service will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete-extra-service.php', {
                        method: 'POST',
                        headers: {'Content-Type':'application/x-www-form-urlencoded'},
                        body: 'id=' + id
                    })
                    .then(res => res.text())
                    .then(resp => {
                        if(resp.trim() === 'success'){
                            showToast('success','Deleted successfully!');
                            loadExtraServices();
                        } else {
                            showToast('error','Error deleting: '+resp);
                        }
                    });
                }
            });
        });
    });
}

// Save service
saveBtn.addEventListener('click', e => {
    e.preventDefault();
    const formData = new FormData(document.getElementById('serviceForm'));

    fetch('update-extra-service.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(resp => {
        if(resp.trim() === 'success'){
            closeModal();
            loadExtraServices();
            showToast('success','Saved successfully!');
        } else {
            showToast('error','Error: '+resp);
        }
    });
});

// Initial load
loadExtraServices();
</script>
</body>
</html>
