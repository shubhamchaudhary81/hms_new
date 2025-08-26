<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>gallery</title>
</head>

<body>
    <!-- ===== GALLERY SECTION ===== -->
<section id="gallery" class="py-5 bg-light">
  <div class="container">
    <!-- Section Title -->
    <div class="text-center mb-5">
      <h2 class="fw-bold">Our Gallery</h2>
      <p class="text-muted">Discover our elegant rooms, dining areas, event spaces, and wellness facilities.</p>
    </div>

    <!-- Filter Buttons -->
    <div class="d-flex justify-content-center mb-4 flex-wrap gap-2">
      <button class="btn btn-outline-primary active" data-filter="all">All</button>
      <button class="btn btn-outline-primary" data-filter="rooms">Rooms</button>
      <button class="btn btn-outline-primary" data-filter="dining">Dining</button>
      <button class="btn btn-outline-primary" data-filter="hall">Event Halls</button>
      <button class="btn btn-outline-primary" data-filter="wellness">Wellness</button>
    </div>

    <!-- Gallery Grid -->
    <div class="row g-4">
      <!-- Example Item -->
      <div class="col-md-4 gallery-item rooms">
        <a href="images/room1.jpg" data-lightbox="gallery" data-title="Deluxe Room">
          <img src="images/room1.jpg" class="img-fluid rounded shadow-sm" alt="Room">
        </a>
      </div>

      <div class="col-md-4 gallery-item dining">
        <a href="images/dining1.jpg" data-lightbox="gallery" data-title="Fine Dining">
          <img src="images/dining1.jpg" class="img-fluid rounded shadow-sm" alt="Dining">
        </a>
      </div>

      <div class="col-md-4 gallery-item hall">
        <a href="images/hall1.jpg" data-lightbox="gallery" data-title="Conference Hall">
          <img src="images/hall1.jpg" class="img-fluid rounded shadow-sm" alt="Hall">
        </a>
      </div>

      <div class="col-md-4 gallery-item wellness">
        <a href="images/spa1.jpg" data-lightbox="gallery" data-title="Spa & Wellness">
          <img src="images/spa1.jpg" class="img-fluid rounded shadow-sm" alt="Spa">
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===== Lightbox & Filter Script ===== -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox-plus-jquery.min.js"></script>

<script>
  // Simple filter script
  const filterBtns = document.querySelectorAll("[data-filter]");
  const items = document.querySelectorAll(".gallery-item");

  filterBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      filterBtns.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const filter = btn.getAttribute("data-filter");
      items.forEach(item => {
        if (filter === "all" || item.classList.contains(filter)) {
          item.style.display = "block";
        } else {
          item.style.display = "none";
        }
      });
    });
  });
</script>

</body>

</html>