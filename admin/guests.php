<?php
session_start();
if ($_SESSION['admin_id'] == "" || $_SESSION['admin_name'] == "") {
    header("Location: ../login.php");
    exit();
} 
include_once '../config/configdatabse.php';

$headerTitle = "Guest Management";
$headerSubtitle = "Manage guest information and stay details.";
$buttonText = "Add New Guest";
$buttonLink = "add-guest.php"; // <- Your target page
$showButton = true; // Show the button in the header

// Fetch guests with their latest reservation/booking and room details
$guests = [];

$sql = "
    SELECT 
        c.id AS customer_id,
        c.first_name,
        c.last_name,
        c.email,
        c.number,
        c.dob,
        c.gender,
        c.province,
        c.district,
        c.city,
        c.profile_pic,
        c.registered_at,
        r.reservation_id,
        r.requested_check_in_date,
        r.requested_check_out_date,
        r.status AS reservation_status,
        b.status AS booking_status,
        b.actual_check_in,
        b.actual_check_out,
        rm.room_number,
        rt.room_type_name
    FROM Customers c
    LEFT JOIN (
        SELECT r1.*
        FROM Reservations r1
        INNER JOIN (
            SELECT customer_id, MAX(reservation_date) AS max_res_date
            FROM Reservations
            GROUP BY customer_id
        ) r2 ON r1.customer_id = r2.customer_id AND r1.reservation_date = r2.max_res_date
    ) r ON r.customer_id = c.id
    LEFT JOIN Bookings b ON b.reservation_id = r.reservation_id
    LEFT JOIN Room rm ON rm.room_id = b.room_id
    LEFT JOIN RoomType rt ON rt.room_type_id = rm.room_type
    ORDER BY c.registered_at DESC
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $guests[] = $row;
    }
}

function getInitials($firstName, $lastName) {
    $fi = $firstName !== null && $firstName !== '' ? strtoupper(substr($firstName, 0, 1)) : '';
    $li = $lastName !== null && $lastName !== '' ? strtoupper(substr($lastName, 0, 1)) : '';
    $initials = $fi . $li;
    return $initials !== '' ? $initials : 'GU';
}

function formatDateSafe($dateStr) {
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if ($ts === false) return '';
    return date('M d, Y', $ts);
}

function computeStatusClass($bookingStatus, $reservationStatus) {
    $bs = strtolower((string)$bookingStatus);
    $rs = strtolower((string)$reservationStatus);
    if (in_array($bs, ['active', 'checked in', 'checked_in', 'ongoing'])) {
        return ['checked-in', 'Checked In'];
    }
    if (in_array($bs, ['completed', 'checked out', 'checked_out'])) {
        return ['checked-out', 'Checked Out'];
    }
    if (in_array($rs, ['reserved', 'confirmed', 'pending'])) {
        return ['reserved', ucfirst($reservationStatus) ?: 'Reserved'];
    }
    if ($bookingStatus) {
        return ['reserved', $bookingStatus];
    }
    if ($reservationStatus) {
        return ['reserved', $reservationStatus];
    }
    return ['reserved', 'Registered'];
}
?>

<?php include '../include/admin/header.php'; ?>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header-content.php'; ?>
   

		<div class="filters-section">
			<div class="filter-group">
				<input type="text" class="filter-input" placeholder="Search guests...">
			</div>
		</div>

		<div class="table-view" id="tableView">
			<table class="guest-table">
				<thead>
					<tr>
						<th>Guest</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Gender</th>
						<th>DOB</th>
						<th>Address</th>
						<th>Registered</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php if (count($guests) > 0): ?>
					<?php foreach ($guests as $g): ?>
						<?php
							$initials = getInitials($g['first_name'] ?? '', $g['last_name'] ?? '');
							$fullName = trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''));
							$email = $g['email'] ?? '';
							$phone = $g['number'] ?? '';
							$address = trim(($g['province'] ?? '') . ', ' . ($g['district'] ?? '') . ', ' . ($g['city'] ?? ''), ' ,');
						?>
						<tr class="guest-row" data-name="<?= htmlspecialchars($fullName) ?>" data-email="<?= htmlspecialchars($email) ?>" data-phone="<?= htmlspecialchars($phone) ?>" data-profile="<?= htmlspecialchars($g['profile_pic'] ?? '') ?>" data-dob="<?= htmlspecialchars($g['dob'] ?? '') ?>" data-gender="<?= htmlspecialchars($g['gender'] ?? '') ?>" data-province="<?= htmlspecialchars($g['province'] ?? '') ?>" data-district="<?= htmlspecialchars($g['district'] ?? '') ?>" data-city="<?= htmlspecialchars($g['city'] ?? '') ?>" data-registered="<?= htmlspecialchars($g['registered_at'] ?? '') ?>">
							<td>
								<div style="display:flex; align-items:center; gap:10px;">
									<div class="guest-avatar" style="width:32px;height:32px;font-size:12px;">
										<?php if (!empty($g['profile_pic'])): ?>
											<img src="../uploads/profile_pics/<?= htmlspecialchars($g['profile_pic']) ?>" alt="Profile" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
										<?php else: ?>
											<?= htmlspecialchars($initials) ?>
										<?php endif; ?>
									</div>
									<div>
										<div class="guest-name" style="margin:0; font-size:14px; line-height:1;"><?= htmlspecialchars($fullName) ?></div>
									</div>
								</div>
							</td>
							<td><?= htmlspecialchars($email) ?></td>
							<td><?= htmlspecialchars($phone) ?></td>
							<td><?= htmlspecialchars($g['gender'] ?? '-') ?></td>
							<td><?= !empty($g['dob']) ? htmlspecialchars(formatDateSafe($g['dob'])) : '-' ?></td>
							<td><?= htmlspecialchars($address ?: '-') ?></td>
							<td><?= !empty($g['registered_at']) ? htmlspecialchars(formatDateSafe($g['registered_at'])) : '-' ?></td>
							<td>
								<button class="btn-secondary view-btn">View</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="8" style="padding:14px; color:#666; text-align:center;">No guests found.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
        
        <!-- Guest Detail Modal -->
        <div id="guestDetailModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
            <div style="background:#fff; width:90%; max-width:520px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #eee;">
                    <h3 style="margin:0; font-size:18px; color:#333;">Guest Details</h3>
                    <button id="guestDetailClose" aria-label="Close" style="background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#666;">&times;</button>
                </div>
                <div id="guestDetailBody" style="padding:16px;"></div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        // const sidebar = document.getElementById('sidebar');
        // const toggleBtn = document.getElementById('toggleBtn');
        // const toggleIcon = toggleBtn?.querySelector('i');

        // if (toggleBtn) {
        //     toggleBtn.addEventListener('click', () => {
        //         sidebar.classList.toggle('collapsed');
                
        //         if (sidebar.classList.contains('collapsed')) {
        //             toggleIcon.classList.remove('fa-chevron-left');
        //             toggleIcon.classList.add('fa-chevron-right');
        //         } else {
        //             toggleIcon.classList.remove('fa-chevron-right');
        //             toggleIcon.classList.add('fa-chevron-left');
        //         }
        //     });
        // }

        // Mobile responsiveness
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('mobile-open');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        // Smooth animations on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
        });

        // Guest card interactions
        const guestCards = document.querySelectorAll('.guest-card');
        guestCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 8px 30px rgba(139, 115, 85, 0.15)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.08)';
            });
        });

        // View toggle and filter functionality (live search)
        const searchInput = document.querySelector('.filter-input');
        const tableView = document.getElementById('tableView');
        const rows = () => Array.from(tableView.querySelectorAll('.guest-row'));

        function normalize(str) { return (str || '').toLowerCase().trim(); }

        function applySearchFilter() {
            const q = normalize(searchInput.value);
            rows().forEach(row => {
                const haystack = [
                    row.getAttribute('data-name'),
                    row.getAttribute('data-email'),
                    row.getAttribute('data-phone'),
                    row.getAttribute('data-city')
                ].map(normalize).join(' ');
                row.style.display = q === '' || haystack.includes(q) ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', applySearchFilter);

        // View Details modal logic (shows customer info)
        const modal = document.getElementById('guestDetailModal');
        const modalBody = document.getElementById('guestDetailBody');
        const modalClose = document.getElementById('guestDetailClose');
        
        function openModalFromEl(el) {
            const name = el.getAttribute('data-name') || '';
            const email = el.getAttribute('data-email') || '';
            const phone = el.getAttribute('data-phone') || '';
            const profile = el.getAttribute('data-profile') || '';
            const dob = el.getAttribute('data-dob') || '';
            const gender = el.getAttribute('data-gender') || '';
            const province = el.getAttribute('data-province') || '';
            const district = el.getAttribute('data-district') || '';
            const city = el.getAttribute('data-city') || '';
            const registered = el.getAttribute('data-registered') || '';

            const imgHtml = profile ? `<img src="../uploads/profile_pics/${profile}" alt="Profile" style="width:92px;height:92px;border-radius:50%;object-fit:cover;">` : `<div class="guest-avatar" style="width:92px;height:92px;font-size:32px;display:flex;align-items:center;justify-content:center;background:#f1ece6;color:#8B7355;border-radius:50%;">${(name||'G U').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase()}</div>`;

            modalBody.innerHTML = `
                <div style="display:flex; gap:16px; align-items:center; margin-bottom:12px;">
                    ${imgHtml}
                    <div>
                        <div style="font-weight:600;font-size:18px;color:#333;">${name}</div>
                        <div style="color:#666;margin-top:2px;">${email}</div>
                        <div style="color:#666;margin-top:2px;">${phone}</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 120px 1fr; row-gap:8px; column-gap:10px;">
                    <div style="color:#888;">DOB</div><div>${dob ? new Date(dob).toLocaleDateString() : '-'}</div>
                    <div style="color:#888;">Gender</div><div>${gender || '-'}</div>
                    <div style="color:#888;">Address</div><div>${[province, district, city].filter(Boolean).join(', ') || '-'}</div>
                    <div style="color:#888;">Registered</div><div>${registered ? new Date(registered).toLocaleDateString() : '-'}</div>
                </div>
            `;

            modal.style.display = 'flex';
        }

        document.querySelectorAll('.guest-row .view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.currentTarget.closest('.guest-row');
                openModalFromEl(row);
            });
        });

        modalClose?.addEventListener('click', () => { modal.style.display = 'none'; });
        modal?.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

        console.log('Guests page initialized with dynamic data and modal');
    </script>
</body>
</html>