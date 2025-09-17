<?php
$headerTitle = "New Reservation";
$headerSubtitle = "Manage all hotel bookings and reservations";
// $buttonText = "New Reservation";
// $buttonLink = "addroom.php";
// $showButton = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Staff Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin/content.css">
    <style>
        :root {
            --primary: #8b7355;
            --primary-light: #a58c6c;
            --primary-dark: #76614a;
            --accent: #5a4c3d;
            --light: #f9f6f2;
            --light-alt: #e8dfd1;
            --dark: #333;
            --gray: #666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        /* ✅ Adjusted for sidebar */
        .main-content {
            margin-left: 260px;
            /* match sidebar width */
            padding: 20px 30px;
            max-width: calc(100% - 260px);
        }

        header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--primary);
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-alt);
        }


        h3 {
            color: var(--accent);
            margin-bottom: 15px;
        }

        .staff-info {
            background-color: var(--light-alt);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reservation-process {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .process-step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .process-step .circle {
            width: 50px;
            height: 50px;
            background-color: var(--light-alt);
            border: 3px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .process-step.active .circle {
            background-color: var(--primary);
            color: white;
        }

        .process-step p {
            font-weight: 500;
            color: var(--primary);
        }

        .process-connector {
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--light-alt);
            z-index: 1;
        }

        .form-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--accent);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-alt);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 600;
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        .btn-container {
            text-align: center;
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .facilities-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }

        .facility-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative;
        }

        .facility-card:hover {
            transform: translateY(-5px);
        }

        .facility-card.selected {
            border: 3px solid var(--primary);
        }

        .facility-card.selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .facility-img {
            height: 180px;
            background-color: var(--light-alt);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 2.5rem;
        }

        .facility-content {
            padding: 20px;
        }

        .facility-title {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .facility-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent);
            margin: 10px 0;
        }

        .facility-desc {
            color: var(--gray);
            margin-bottom: 15px;
        }

        .guest-summary {
            background-color: var(--light-alt);
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }

        .guest-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--accent);
        }

        .selected-facilities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .facility-badge {
            background-color: var(--primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
            background-color: var(--light-alt);
            color: var(--accent);
            border-radius: 10px 10px 0 0;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .reservation-process {
                flex-direction: column;
                gap: 30px;
            }

            .process-connector {
                display: none;
            }

            .btn-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content"><!-- ✅ wrapped all content here -->
        <?php include 'header-content.php'; ?>

        <div class="staff-info">
            <div>
                <strong>Staff ID:</strong> SH1258 | <strong>Name:</strong> Emily Johnson
            </div>
            <div>
                <button class="btn btn-outline">Save Progress</button>
            </div>
        </div>

        <div class="reservation-process">
            <div class="process-connector"></div>
            <div class="process-step active">
                <div class="circle">1</div>
                <p>Guest Details</p>
            </div>
            <div class="process-step">
                <div class="circle">2</div>
                <p>Select Facilities</p>
            </div>
            <div class="process-step">
                <div class="circle">3</div>
                <p>Confirmation</p>
            </div>
        </div>
        <div class="form-section">
            <h2>Guest Information</h2>
            <form id="reservation-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">First Name *</label>
                        <input type="text" id="first-name" required>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name *</label>
                        <input type="text" id="last-name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="check-in">Check-in Date *</label>
                        <input type="date" id="check-in" required>
                    </div>
                    <div class="form-group">
                        <label for="check-out">Check-out Date *</label>
                        <input type="date" id="check-out" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="guests">Number of Guests *</label>
                        <select id="guests" required>
                            <option value="">Select guests</option>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="room-type">Room Type *</label>
                        <select id="room-type" required>
                            <option value="">Select room type</option>
                            <option value="standard">Standard Room ($120/night)</option>
                            <option value="deluxe">Deluxe Room ($180/night)</option>
                            <option value="suite">Executive Suite ($280/night)</option>
                            <option value="presidential">Presidential Suite ($450/night)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special-requests">Special Requests</label>
                    <textarea id="special-requests" rows="3"></textarea>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn btn-outline">Clear Form</button>
                    <button type="submit" class="btn">Proceed to Facility Selection <i
                            class="fas fa-arrow-right"></i></button>
                </div>
            </form>
        </div>

        <h2>Available Facilities</h2>
        <p>Click on facilities to add them to the guest's reservation</p>

        <div class="facilities-section">
            <div class="facility-card" data-facility="pool" data-price="0">
                <div class="facility-img">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Infinity Pool</h3>
                    <div class="facility-price">Complimentary for guests</div>
                    <p class="facility-desc">Enjoy our stunning infinity pool with panoramic views of the city skyline.
                    </p>
                </div>
            </div>

            <div class="facility-card" data-facility="spa" data-price="85">
                <div class="facility-img">
                    <i class="fas fa-spa"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Luxury Spa</h3>
                    <div class="facility-price">From $85 per treatment</div>
                    <p class="facility-desc">Relax and rejuvenate with our wide range of spa treatments and therapies.
                    </p>
                </div>
            </div>

            <div class="facility-card" data-facility="gym" data-price="0">
                <div class="facility-img">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Fitness Center</h3>
                    <div class="facility-price">Complimentary for guests</div>
                    <p class="facility-desc">State-of-the-art fitness equipment available 24/7 for your convenience.</p>
                </div>
            </div>

            <div class="facility-card" data-facility="dining" data-price="35">
                <div class="facility-img">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Fine Dining</h3>
                    <div class="facility-price">From $35 per person</div>
                    <p class="facility-desc">Experience gourmet cuisine at our award-winning restaurant.</p>
                </div>
            </div>

            <div class="facility-card" data-facility="concierge" data-price="0">
                <div class="facility-img">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Concierge Service</h3>
                    <div class="facility-price">Complimentary for guests</div>
                    <p class="facility-desc">Our concierge team is available to assist with all your needs and
                        arrangements.</p>
                </div>
            </div>

            <div class="facility-card" data-facility="wifi" data-price="0">
                <div class="facility-img">
                    <i class="fas fa-wifi"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">High-Speed WiFi</h3>
                    <div class="facility-price">Complimentary for guests</div>
                    <p class="facility-desc">Stay connected with our high-speed internet access throughout the hotel.
                    </p>
                </div>
            </div>

            <div class="facility-card" data-facility="parking" data-price="25">
                <div class="facility-img">
                    <i class="fas fa-parking"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Valet Parking</h3>
                    <div class="facility-price">$25 per day</div>
                    <p class="facility-desc">Secure valet parking service with in-and-out privileges.</p>
                </div>
            </div>

            <div class="facility-card" data-facility="business" data-price="50">
                <div class="facility-img">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="facility-content">
                    <h3 class="facility-title">Business Center</h3>
                    <div class="facility-price">$50 per day access</div>
                    <p class="facility-desc">Fully equipped business center with meeting rooms and printing services.
                    </p>
                </div>
            </div>
        </div>

        <div class="guest-summary">
            <h2>Guest Reservation Summary</h2>
            <div class="guest-details">
                <div>
                    <div class="detail-item">
                        <span class="detail-label">Guest Name:</span>
                        <span id="summary-name">Not provided yet</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Contact:</span>
                        <span id="summary-contact">Not provided yet</span>
                    </div>
                </div>
                <div>
                    <div class="detail-item">
                        <span class="detail-label">Stay Duration:</span>
                        <span id="summary-dates">Not selected yet</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Room Type:</span>
                        <span id="summary-room">Not selected yet</span>
                    </div>
                </div>
                <div>
                    <div class="detail-item">
                        <span class="detail-label">Total Guests:</span>
                        <span id="summary-guests">Not specified yet</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Additional Charges:</span>
                        <span id="summary-charges">$0</span>
                    </div>
                </div>
            </div>

            <h3>Selected Facilities</h3>
            <div class="selected-facilities" id="selected-facilities-list">
                <div class="facility-badge">
                    <span>No facilities selected yet</span>
                </div>
            </div>

            <div class="btn-container">
                <button class="btn">Complete Reservation</button>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>© 2023 Luxury Haven Hotel. Staff Reservation System v2.5</p>
            <p>For support, contact IT Department at extension 5050 or email support@luxuryhaven.com</p>
        </div>
    </footer>

    <script>
        // Set minimum date for check-in to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check-in').setAttribute('min', today);

        // Set minimum for check-out based on check-in date
        document.getElementById('check-in').addEventListener('change', function () {
            const checkInDate = this.value;
            document.getElementById('check-out').setAttribute('min', checkInDate);
            updateSummary();
        });

        // Form submission
        document.getElementById('reservation-form').addEventListener('submit', function (e) {
            e.preventDefault();
            updateSummary();
            alert('Guest information saved. Please select facilities for the reservation.');
            // Scroll to facilities section
            document.querySelector('.facilities-section').scrollIntoView({ behavior: 'smooth' });
        });

        // Facility selection
        const facilityCards = document.querySelectorAll('.facility-card');
        const selectedFacilities = new Set();

        facilityCards.forEach(card => {
            card.addEventListener('click', function () {
                this.classList.toggle('selected');
                const facility = this.getAttribute('data-facility');

                if (selectedFacilities.has(facility)) {
                    selectedFacilities.delete(facility);
                } else {
                    selectedFacilities.add(facility);
                }

                updateSelectedFacilities();
            });
        });

        // Update selected facilities list
        function updateSelectedFacilities() {
            const facilitiesList = document.getElementById('selected-facilities-list');
            facilitiesList.innerHTML = '';

            if (selectedFacilities.size === 0) {
                facilitiesList.innerHTML = '<div class="facility-badge"><span>No facilities selected yet</span></div>';
            } else {
                let totalCharges = 0;

                selectedFacilities.forEach(facility => {
                    const card = document.querySelector(`[data-facility="${facility}"]`);
                    const name = card.querySelector('.facility-title').textContent;
                    const price = parseInt(card.getAttribute('data-price'));

                    totalCharges += price;

                    const badge = document.createElement('div');
                    badge.className = 'facility-badge';
                    badge.innerHTML = `<span>${name}</span> <small>${price > 0 ? '$' + price : 'Complimentary'}</small>`;
                    facilitiesList.appendChild(badge);
                });

                document.getElementById('summary-charges').textContent = '$' + totalCharges;
            }
        }

        // Update reservation summary
        function updateSummary() {
            const firstName = document.getElementById('first-name').value;
            const lastName = document.getElementById('last-name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const checkIn = document.getElementById('check-in').value;
            const checkOut = document.getElementById('check-out').value;
            const guests = document.getElementById('guests').value;
            const roomType = document.getElementById('room-type').value;

            if (firstName && lastName) {
                document.getElementById('summary-name').textContent = `${firstName} ${lastName}`;
            }

            if (email || phone) {
                document.getElementById('summary-contact').textContent = `${email} | ${phone}`;
            }

            if (checkIn && checkOut) {
                document.getElementById('summary-dates').textContent = `${checkIn} to ${checkOut}`;
            }

            if (guests) {
                document.getElementById('summary-guests').textContent = `${guests} Guest${guests > 1 ? 's' : ''}`;
            }

            if (roomType) {
                const roomText = document.getElementById('room-type').options[document.getElementById('room-type').selectedIndex].text;
                document.getElementById('summary-room').textContent = roomText;
            }
        }

        // Initialize with today's date
        document.getElementById('check-in').value = today;

        // Set check-out to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('check-out').value = tomorrow.toISOString().split('T')[0];
    </script>
</body>

</html>