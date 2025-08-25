
<?php
include_once '../config/configdatabse.php';

$headerTitle = "Billing & Invoices";
$headerSubtitle = "Manage guest billing, invoices, and payment processing.";
$buttonText = "Create Invoice";
$buttonLink = "create-invoice.php";
$showButton = true;

// Fetch invoices with customer and booking details
$invoices = [];

$sql = "
    SELECT 
        i.invoice_id,
        i.invoice_date,
        i.total_amount,
        i.amount_paid,
        i.balance_due,
        i.status,
        i.notes,
        i.due_date,
        c.first_name,
        c.last_name,
        c.email,
        c.number,
        b.booking_id,
        b.actual_check_in,
        b.actual_check_out,
        rm.room_number,
        rt.room_type_name,
        rm.price_per_night
    FROM Invoices i
    LEFT JOIN Bookings b ON i.booking_id = b.booking_id
    LEFT JOIN Reservations r ON b.reservation_id = r.reservation_id
    LEFT JOIN Customers c ON r.customer_id = c.id
    LEFT JOIN Room rm ON b.room_id = rm.room_id
    LEFT JOIN RoomType rt ON rt.room_type_id = rm.room_type
    ORDER BY i.invoice_date DESC
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
}

function formatDateSafe($dateStr) {
    if (!$dateStr) return '';
    $ts = strtotime($dateStr);
    if ($ts === false) return '';
    return date('M d, Y', $ts);
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function getStatusClass($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'paid':
            return 'paid';
        case 'pending':
            return 'pending';
        case 'overdue':
            return 'overdue';
        case 'draft':
            return 'draft';
        default:
            return 'pending';
    }
}

function calculateNights($checkIn, $checkOut) {
    if (!$checkIn || !$checkOut) return 0;
    $start = new DateTime($checkIn);
    $end = new DateTime($checkOut);
    $diff = $start->diff($end);
    return $diff->days;
}
?>

<?php include '../include/admin/header.php'; ?>
<body>
   <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header-content.php'; ?>

        <div class="filters-section">
            <div class="filter-group">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                    <option value="draft">Draft</option>
                </select>
                <input type="date" class="filter-input" id="fromDate" placeholder="From Date">
                <input type="date" class="filter-input" id="toDate" placeholder="To Date">
                <input type="text" class="filter-input" id="searchInput" placeholder="Search invoices...">
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <strong>Success!</strong> <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <strong>Error!</strong> <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="billing-grid" id="billingGrid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px;">
            <?php if (count($invoices) > 0): ?>
                <?php foreach ($invoices as $inv): ?>
                    <?php
                        $fullName = trim(($inv['first_name'] ?? '') . ' ' . ($inv['last_name'] ?? ''));
                        $nights = calculateNights($inv['actual_check_in'], $inv['actual_check_out']);
                        $statusClass = getStatusClass($inv['status']);
                        $roomInfo = '';
                        if (!empty($inv['room_number'])) {
                            $roomInfo = htmlspecialchars($inv['room_number']);
                            if (!empty($inv['room_type_name'])) {
                                $roomInfo .= ' - ' . htmlspecialchars($inv['room_type_name']);
                            }
                        }
                        $taxAmount = $inv['total_amount'] * 0.1; // Assuming 10% tax
                        $roomRate = $inv['price_per_night'] ?? 0;
                    ?>
                                         <div class="invoice-card" style="padding: 16px; min-height: 280px;"
                          data-invoice="<?= htmlspecialchars($inv['invoice_id']) ?>"
                          data-guest="<?= htmlspecialchars($fullName) ?>"
                          data-email="<?= htmlspecialchars($inv['email'] ?? '') ?>"
                          data-phone="<?= htmlspecialchars($inv['number'] ?? '') ?>"
                          data-status="<?= htmlspecialchars($inv['status']) ?>"
                          data-total="<?= htmlspecialchars($inv['total_amount']) ?>"
                          data-paid="<?= htmlspecialchars($inv['amount_paid']) ?>"
                          data-balance="<?= htmlspecialchars($inv['balance_due']) ?>"
                          data-room="<?= htmlspecialchars($roomInfo) ?>"
                          data-checkin="<?= htmlspecialchars($inv['actual_check_in'] ?? '') ?>"
                          data-checkout="<?= htmlspecialchars($inv['actual_check_out'] ?? '') ?>"
                          data-nights="<?= $nights ?>"
                          data-rate="<?= htmlspecialchars($roomRate) ?>"
                          data-invoice-date="<?= htmlspecialchars($inv['invoice_date'] ?? '') ?>"
                          data-due-date="<?= htmlspecialchars($inv['due_date'] ?? '') ?>"
                          data-notes="<?= htmlspecialchars($inv['notes'] ?? '') ?>"
                          data-customer-id="<?= htmlspecialchars($inv['customer_id'] ?? '') ?>"
                          data-booking-id="<?= htmlspecialchars($inv['booking_id'] ?? '') ?>">
                        <div class="invoice-header" style="margin-bottom: 12px;">
                            <span class="invoice-number" style="font-size: 14px;">INV-<?= str_pad($inv['invoice_id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span class="invoice-status <?= $statusClass ?>" style="font-size: 11px; padding: 4px 8px;"><?= ucfirst($inv['status']) ?></span>
                        </div>
                        <div class="invoice-details" style="margin-bottom: 12px;">
                            <div class="guest-info" style="margin-bottom: 8px;">
                                <div class="guest-name" style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($fullName) ?></div>
                                <div class="guest-room" style="font-size: 12px; color: #666;"><?= htmlspecialchars($roomInfo ?: 'No room assigned') ?></div>
                            </div>
                            <div class="billing-info" style="font-size: 11px;">
                                <div class="info-row" style="margin-bottom: 4px;">
                                    <span class="info-label">Check-in:</span>
                                    <span class="info-value"><?= $inv['actual_check_in'] ? formatDateSafe($inv['actual_check_in']) : 'Not checked in' ?></span>
                                </div>
                                <div class="info-row" style="margin-bottom: 4px;">
                                    <span class="info-label">Check-out:</span>
                                    <span class="info-value"><?= $inv['actual_check_out'] ? formatDateSafe($inv['actual_check_out']) : 'Not checked out' ?></span>
                                </div>
                                <div class="info-row" style="margin-bottom: 4px;">
                                    <span class="info-label">Nights:</span>
                                    <span class="info-value"><?= $nights ?></span>
                                </div>
                                <div class="info-row" style="margin-bottom: 4px;">
                                    <span class="info-label">Room Rate:</span>
                                    <span class="info-value"><?= formatCurrency($roomRate) ?>/night</span>
                                </div>
                            </div>
                            <div class="total-amount" style="font-size: 13px; font-weight: 600; margin-top: 8px;">Total: <?= formatCurrency($inv['total_amount']) ?></div>
                        </div>
                                                 <div class="invoice-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                             <button class="btn-secondary view-invoice-btn" style="font-size: 11px; padding: 6px 10px;">View</button>
                             <button class="btn-outline download-pdf-btn" style="font-size: 11px; padding: 6px 10px;">Download PDF</button>
                             <?php if ($inv['status'] !== 'paid'): ?>
                                 <button class="btn-success process-payment-btn" style="font-size: 11px; padding: 6px 10px; background: #28a745; color: white; border: none; border-radius: 4px;">Pay</button>
                             <?php endif; ?>
                             <?php if ($inv['status'] === 'pending'): ?>
                                 <button class="btn-primary send-invoice-btn" style="font-size: 11px; padding: 6px 10px;">Send</button>
                             <?php elseif ($inv['status'] === 'overdue'): ?>
                                 <button class="btn-primary send-reminder-btn" style="font-size: 11px; padding: 6px 10px;">Remind</button>
                             <?php else: ?>
                                 <button class="btn-outline" style="font-size: 11px; padding: 6px 10px;">Edit</button>
                             <?php endif; ?>
                         </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding:20px; color:#666; text-align:center; grid-column:1/-1;">No invoices found.</div>
            <?php endif; ?>
        </div>

        <!-- Invoice Detail Modal -->
        <div id="invoiceDetailModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
            <div style="background:#fff; width:90%; max-width:600px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #eee;">
                    <h3 style="margin:0; font-size:18px; color:#333;">Invoice Details</h3>
                    <button id="invoiceDetailClose" aria-label="Close" style="background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#666;">&times;</button>
                </div>
                <div id="invoiceDetailBody" style="padding:20px;"></div>
            </div>
        </div>

        <!-- Download PDF Modal -->
        <div id="downloadPdfModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
            <div style="background:#fff; width:90%; max-width:500px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #eee;">
                    <h3 style="margin:0; font-size:18px; color:#333;">Download Invoice PDF</h3>
                    <button id="downloadPdfClose" aria-label="Close" style="background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#666;">&times;</button>
                </div>
                <div style="padding:20px;">
                    <form id="downloadPdfForm" method="POST" action="generate_invoice_pdf.php">
                        <input type="hidden" id="pdfInvoiceId" name="invoice_id" value="">
                        <input type="hidden" id="pdfGuestName" name="guest_name" value="">
                        
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600; color:#333;">Invoice Information</label>
                            <div id="pdfInvoiceInfo" style="background:#f8f9fa; padding:12px; border-radius:8px; font-size:14px;"></div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label for="pdfFormat" style="display:block; margin-bottom:8px; font-weight:600; color:#333;">PDF Format</label>
                            <select id="pdfFormat" name="format" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                                <option value="detailed">Detailed Invoice (Full breakdown)</option>
                                <option value="summary">Summary Invoice (Basic info)</option>
                                <option value="receipt">Payment Receipt</option>
                            </select>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label for="pdfIncludeLogo" style="display:flex; align-items:center; margin-bottom:8px; font-weight:600; color:#333;">
                                <input type="checkbox" id="pdfIncludeLogo" name="include_logo" value="1" style="margin-right:8px;">
                                Include Hotel Logo
                            </label>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label for="pdfIncludeNotes" style="display:flex; align-items:center; margin-bottom:8px; font-weight:600; color:#333;">
                                <input type="checkbox" id="pdfIncludeNotes" name="include_notes" value="1" style="margin-right:8px;">
                                Include Notes/Comments
                            </label>
                        </div>

                        <div style="display:flex; gap:12px; justify-content:flex-end;">
                            <button type="button" id="cancelPdfDownload" style="padding:10px 20px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer; font-size:14px;">Cancel</button>
                            <button type="submit" style="padding:10px 20px; background:#8B7355; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">Generate & Download PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div id="paymentModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
            <div style="background:#fff; width:90%; max-width:500px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #eee;">
                    <h3 style="margin:0; font-size:18px; color:#333;">Process Payment</h3>
                    <button id="paymentModalClose" aria-label="Close" style="background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#666;">&times;</button>
                </div>
                <div style="padding:20px;">
                    <form id="paymentForm" method="POST" action="process_payment.php">
                        <input type="hidden" id="paymentInvoiceId" name="invoice_id" value="">
                        <input type="hidden" id="paymentCustomerId" name="customer_id" value="">
                        <input type="hidden" id="paymentBookingId" name="booking_id" value="">
                        
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:8px; font-weight:600; color:#333;">Invoice Information</label>
                            <div id="paymentInvoiceInfo" style="background:#f8f9fa; padding:12px; border-radius:8px; font-size:14px;"></div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label for="paymentAmount" style="display:block; margin-bottom:8px; font-weight:600; color:#333;">Payment Amount</label>
                            <input type="number" id="paymentAmount" name="payment_amount" step="0.01" min="0" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                        </div>

                        <div style="margin-bottom:20px;">
                            <label for="paymentMethod" style="display:block; margin-bottom:8px; font-weight:600; color:#333;">Payment Method</label>
                            <select id="paymentMethod" name="payment_method" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online_payment">Online Payment</option>
                            </select>
                        </div>

                        <div style="display:flex; gap:12px; justify-content:flex-end;">
                            <button type="button" id="cancelPayment" style="padding:10px 20px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer; font-size:14px;">Cancel</button>
                            <button type="submit" style="padding:10px 20px; background:#28a745; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600;">Process Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Invoice card interactions
        const invoiceCards = document.querySelectorAll('.invoice-card');
        invoiceCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 8px 30px rgba(139, 115, 85, 0.15)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 2px 20px rgba(139, 115, 85, 0.08)';
            });
        });

        // Filter and search functionality
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const billingGrid = document.getElementById('billingGrid');

        function normalize(str) { return (str || '').toLowerCase().trim(); }

        function applyFilters() {
            const searchTerm = normalize(searchInput.value);
            const statusTerm = normalize(statusFilter.value);
            const fromDateVal = fromDate.value;
            const toDateVal = toDate.value;

            invoiceCards.forEach(card => {
                const guest = normalize(card.getAttribute('data-guest'));
                const email = normalize(card.getAttribute('data-email'));
                const invoice = normalize(card.getAttribute('data-invoice'));
                const status = normalize(card.getAttribute('data-status'));
                const invoiceDate = card.getAttribute('data-invoice-date');

                let show = true;

                // Search filter
                if (searchTerm && !guest.includes(searchTerm) && !email.includes(searchTerm) && !invoice.includes(searchTerm)) {
                    show = false;
                }

                // Status filter
                if (statusTerm && status !== statusTerm) {
                    show = false;
                }

                // Date range filter
                if (fromDateVal && invoiceDate < fromDateVal) {
                    show = false;
                }
                if (toDateVal && invoiceDate > toDateVal) {
                    show = false;
                }

                card.style.display = show ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', applyFilters);
        statusFilter?.addEventListener('change', applyFilters);
        fromDate?.addEventListener('change', applyFilters);
        toDate?.addEventListener('change', applyFilters);

        // Invoice detail modal
        const modal = document.getElementById('invoiceDetailModal');
        const modalBody = document.getElementById('invoiceDetailBody');
        const modalClose = document.getElementById('invoiceDetailClose');

        function openInvoiceModal(card) {
            const invoiceId = card.getAttribute('data-invoice');
            const guest = card.getAttribute('data-guest');
            const email = card.getAttribute('data-email');
            const phone = card.getAttribute('data-phone');
            const status = card.getAttribute('data-status');
            const total = card.getAttribute('data-total');
            const paid = card.getAttribute('data-paid');
            const balance = card.getAttribute('data-balance');
            const room = card.getAttribute('data-room');
            const checkin = card.getAttribute('data-checkin');
            const checkout = card.getAttribute('data-checkout');
            const nights = card.getAttribute('data-nights');
            const rate = card.getAttribute('data-rate');
            const invoiceDate = card.getAttribute('data-invoice-date');
            const dueDate = card.getAttribute('data-due-date');
            const notes = card.getAttribute('data-notes');

            modalBody.innerHTML = `
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div>
                        <h4 style="margin:0 0 12px 0; color:#333;">Invoice Information</h4>
                        <div style="display:grid; grid-template-columns: 120px 1fr; row-gap:8px; column-gap:10px; font-size:14px;">
                            <div style="color:#888;">Invoice #</div><div style="font-weight:600;">INV-${invoiceId.padStart(6, '0')}</div>
                            <div style="color:#888;">Date</div><div>${invoiceDate ? new Date(invoiceDate).toLocaleDateString() : '-'}</div>
                            <div style="color:#888;">Due Date</div><div>${dueDate ? new Date(dueDate).toLocaleDateString() : '-'}</div>
                            <div style="color:#888;">Status</div><div><span class="invoice-status ${status.toLowerCase()}">${status}</span></div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin:0 0 12px 0; color:#333;">Guest Information</h4>
                        <div style="display:grid; grid-template-columns: 120px 1fr; row-gap:8px; column-gap:10px; font-size:14px;">
                            <div style="color:#888;">Name</div><div style="font-weight:600;">${guest}</div>
                            <div style="color:#888;">Email</div><div>${email || '-'}</div>
                            <div style="color:#888;">Phone</div><div>${phone || '-'}</div>
                            <div style="color:#888;">Room</div><div>${room || '-'}</div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:20px; padding-top:20px; border-top:1px solid #eee;">
                    <h4 style="margin:0 0 12px 0; color:#333;">Stay Details</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; font-size:14px;">
                        <div>
                            <div style="color:#888;">Check-in</div>
                            <div style="font-weight:600;">${checkin ? new Date(checkin).toLocaleDateString() : '-'}</div>
                        </div>
                        <div>
                            <div style="color:#888;">Check-out</div>
                            <div style="font-weight:600;">${checkout ? new Date(checkout).toLocaleDateString() : '-'}</div>
                        </div>
                        <div>
                            <div style="color:#888;">Nights</div>
                            <div style="font-weight:600;">${nights}</div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:20px; padding-top:20px; border-top:1px solid #eee;">
                    <h4 style="margin:0 0 12px 0; color:#333;">Billing Summary</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; font-size:14px;">
                        <div>
                            <div style="color:#888;">Total Amount</div>
                            <div style="font-weight:600; font-size:16px; color:#333;">$${parseFloat(total).toFixed(2)}</div>
                        </div>
                        <div>
                            <div style="color:#888;">Amount Paid</div>
                            <div style="font-weight:600; color:#28a745;">$${parseFloat(paid).toFixed(2)}</div>
                        </div>
                        <div>
                            <div style="color:#888;">Balance Due</div>
                            <div style="font-weight:600; color:#dc3545;">$${parseFloat(balance).toFixed(2)}</div>
                        </div>
                    </div>
                    ${notes ? `<div style="margin-top:12px;"><div style="color:#888; font-size:12px;">Notes</div><div style="font-size:14px;">${notes}</div></div>` : ''}
                </div>
            `;

            modal.style.display = 'flex';
        }

        // Event listeners for invoice actions
        document.querySelectorAll('.view-invoice-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.currentTarget.closest('.invoice-card');
                openInvoiceModal(card);
            });
        });

        document.querySelectorAll('.send-invoice-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const card = e.currentTarget.closest('.invoice-card');
                const invoiceId = card.getAttribute('data-invoice');
                alert(`Sending invoice INV-${invoiceId.padStart(6, '0')} to guest...`);
            });
        });

        document.querySelectorAll('.send-reminder-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const card = e.currentTarget.closest('.invoice-card');
                const invoiceId = card.getAttribute('data-invoice');
                alert(`Sending payment reminder for invoice INV-${invoiceId.padStart(6, '0')}...`);
            });
        });

        // Download PDF Modal functionality
        const downloadPdfModal = document.getElementById('downloadPdfModal');
        const downloadPdfClose = document.getElementById('downloadPdfClose');
        const cancelPdfDownload = document.getElementById('cancelPdfDownload');
        const pdfInvoiceId = document.getElementById('pdfInvoiceId');
        const pdfGuestName = document.getElementById('pdfGuestName');
        const pdfInvoiceInfo = document.getElementById('pdfInvoiceInfo');

        function openDownloadPdfModal(card) {
            const invoiceId = card.getAttribute('data-invoice');
            const guest = card.getAttribute('data-guest');
            const total = card.getAttribute('data-total');
            const invoiceDate = card.getAttribute('data-invoice-date');
            const status = card.getAttribute('data-status');

            pdfInvoiceId.value = invoiceId;
            pdfGuestName.value = guest;
            pdfInvoiceInfo.innerHTML = `
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:13px;">
                    <div><strong>Invoice:</strong> INV-${invoiceId.padStart(6, '0')}</div>
                    <div><strong>Guest:</strong> ${guest}</div>
                    <div><strong>Date:</strong> ${invoiceDate ? new Date(invoiceDate).toLocaleDateString() : '-'}</div>
                    <div><strong>Total:</strong> $${parseFloat(total).toFixed(2)}</div>
                    <div><strong>Status:</strong> ${status}</div>
                </div>
            `;

            downloadPdfModal.style.display = 'flex';
        }

        document.querySelectorAll('.download-pdf-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const card = e.currentTarget.closest('.invoice-card');
                openDownloadPdfModal(card);
            });
        });

                 downloadPdfClose?.addEventListener('click', () => { downloadPdfModal.style.display = 'none'; });
         cancelPdfDownload?.addEventListener('click', () => { downloadPdfModal.style.display = 'none'; });
         downloadPdfModal?.addEventListener('click', (e) => { if (e.target === downloadPdfModal) downloadPdfModal.style.display = 'none'; });

         // Payment Modal functionality
         const paymentModal = document.getElementById('paymentModal');
         const paymentModalClose = document.getElementById('paymentModalClose');
         const cancelPayment = document.getElementById('cancelPayment');
         const paymentInvoiceId = document.getElementById('paymentInvoiceId');
         const paymentCustomerId = document.getElementById('paymentCustomerId');
         const paymentBookingId = document.getElementById('paymentBookingId');
         const paymentInvoiceInfo = document.getElementById('paymentInvoiceInfo');
         const paymentAmount = document.getElementById('paymentAmount');

         function openPaymentModal(card) {
             const invoiceId = card.getAttribute('data-invoice');
             const guest = card.getAttribute('data-guest');
             const total = card.getAttribute('data-total');
             const paid = card.getAttribute('data-paid');
             const balance = card.getAttribute('data-balance');
             const customerId = card.getAttribute('data-customer-id');
             const bookingId = card.getAttribute('data-booking-id');

             paymentInvoiceId.value = invoiceId;
             paymentCustomerId.value = customerId;
             paymentBookingId.value = bookingId;
             paymentAmount.value = parseFloat(balance).toFixed(2);
             paymentAmount.max = balance;

             paymentInvoiceInfo.innerHTML = `
                 <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; font-size:13px;">
                     <div><strong>Invoice:</strong> INV-${invoiceId.padStart(6, '0')}</div>
                     <div><strong>Guest:</strong> ${guest}</div>
                     <div><strong>Total Amount:</strong> $${parseFloat(total).toFixed(2)}</div>
                     <div><strong>Amount Paid:</strong> $${parseFloat(paid).toFixed(2)}</div>
                     <div><strong>Balance Due:</strong> $${parseFloat(balance).toFixed(2)}</div>
                 </div>
             `;

             paymentModal.style.display = 'flex';
         }

         document.querySelectorAll('.process-payment-btn').forEach(btn => {
             btn.addEventListener('click', (e) => {
                 e.preventDefault();
                 const card = e.currentTarget.closest('.invoice-card');
                 openPaymentModal(card);
             });
         });

         paymentModalClose?.addEventListener('click', () => { paymentModal.style.display = 'none'; });
         cancelPayment?.addEventListener('click', () => { paymentModal.style.display = 'none'; });
         paymentModal?.addEventListener('click', (e) => { if (e.target === paymentModal) paymentModal.style.display = 'none'; });

         modalClose?.addEventListener('click', () => { modal.style.display = 'none'; });
         modal?.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

        console.log('Billing page initialized with dynamic data, card design, and PDF download functionality');
    </script>
</body>
</html>
