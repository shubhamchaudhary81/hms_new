// Global variables
let currentStage = 1
const totalStages = 5
let formData = {}
let uploadedImages = []

// DOM Elements
const prevBtn = document.getElementById("prevBtn")
const nextBtn = document.getElementById("nextBtn")
const submitBtn = document.getElementById("submitBtn")
const form = document.getElementById("roomForm")
const stages = document.querySelectorAll(".form-stage")
const progressSteps = document.querySelectorAll(".progress-step")
const progressLines = document.querySelectorAll(".progress-line")

// Initialize
document.addEventListener("DOMContentLoaded", () => {
  initializeForm()
  setupEventListeners()
  updateProgress()
})

function initializeForm() {
  // Show first stage
  showStage(1)

  // Setup image upload
  setupImageUpload()

  // Setup pricing preview
  setupPricingPreview()

  // Setup amenities tracking
  setupAmenitiesTracking()

  // Setup description character count
  setupDescriptionCounter()
}

function setupEventListeners() {
  // Navigation buttons
  nextBtn.addEventListener("click", handleNext)
  prevBtn.addEventListener("click", handlePrevious)
  submitBtn.addEventListener("click", handleSubmit)

  // Save as draft
  document.getElementById("saveAsDraft").addEventListener("click", saveAsDraft)

  // Form validation on input
  const inputs = form.querySelectorAll("input, select, textarea")
  inputs.forEach((input) => {
    input.addEventListener("blur", validateField)
    input.addEventListener("input", clearError)
  })

  // Sidebar toggle
  const menuToggle = document.getElementById("menuToggle")
  const sidebar = document.getElementById("sidebar")

  menuToggle.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed")
    document.querySelector(".main-content").classList.toggle("expanded")
  })
}

function showStage(stageNumber) {
  // Hide all stages
  stages.forEach((stage) => stage.classList.remove("active"))

  // Show current stage
  const currentStageElement = document.getElementById(`stage-${stageNumber}`)
  if (currentStageElement) {
    currentStageElement.classList.add("active")
  }

  // Update navigation buttons
  updateNavigationButtons()

  // Update progress
  updateProgress()

  // Focus first input in stage
  setTimeout(() => {
    const firstInput = currentStageElement.querySelector("input, select, textarea")
    if (firstInput) {
      firstInput.focus()
    }
  }, 300)
}

function updateNavigationButtons() {
  // Previous button
  prevBtn.style.display = currentStage > 1 ? "inline-flex" : "none"

  // Next/Submit buttons
  if (currentStage < totalStages) {
    nextBtn.style.display = "inline-flex"
    submitBtn.style.display = "none"
  } else {
    nextBtn.style.display = "none"
    submitBtn.style.display = "inline-flex"
  }
}

function updateProgress() {
  progressSteps.forEach((step, index) => {
    const stepNumber = index + 1

    if (stepNumber < currentStage) {
      step.classList.add("completed")
      step.classList.remove("active")
    } else if (stepNumber === currentStage) {
      step.classList.add("active")
      step.classList.remove("completed")
    } else {
      step.classList.remove("active", "completed")
    }
  })

  // Update progress lines
  progressLines.forEach((line, index) => {
    if (index + 1 < currentStage) {
      line.classList.add("completed")
    } else {
      line.classList.remove("completed")
    }
  })
}

function handleNext() {
  if (validateCurrentStage()) {
    saveCurrentStageData()

    if (currentStage < totalStages) {
      currentStage++
      showStage(currentStage)
      showNotification("Progress saved!", "success")
    }
  }
}

function handlePrevious() {
  if (currentStage > 1) {
    saveCurrentStageData()
    currentStage--
    showStage(currentStage)
  }
}

function validateCurrentStage() {
  const currentStageElement = document.getElementById(`stage-${currentStage}`)
  const requiredFields = currentStageElement.querySelectorAll("[required]")
  let isValid = true

  requiredFields.forEach((field) => {
    if (!validateField({ target: field })) {
      isValid = false
    }
  })

  // Stage-specific validation
  switch (currentStage) {
    case 1:
      isValid = validateBasicInfo() && isValid
      break
    case 2:
      isValid = validatePricing() && isValid
      break
    case 5:
      isValid = validateImages() && isValid
      break
  }

  if (!isValid) {
    showNotification("Please fill in all required fields correctly.", "error")
  }

  return isValid
}

function validateBasicInfo() {
  const roomNumber = document.querySelector('[name="room_number"]').value
  const capacity = document.querySelector('[name="capacity"]').value

  if (roomNumber && (roomNumber < 1 || roomNumber > 9999)) {
    showFieldError('[name="room_number"]', "Room number must be between 1 and 9999")
    return false
  }

  if (capacity && (capacity < 1 || capacity > 10)) {
    showFieldError('[name="capacity"]', "Capacity must be between 1 and 10")
    return false
  }

  return true
}

function validatePricing() {
  const basePrice = Number.parseFloat(document.querySelector('[name="price_per_night"]').value)
  const weekendPrice = Number.parseFloat(document.querySelector('[name="weekend_price"]').value) || 0
  const seasonPrice = Number.parseFloat(document.querySelector('[name="season_price"]').value) || 0

  if (basePrice <= 0) {
    showFieldError('[name="price_per_night"]', "Base price must be greater than 0")
    return false
  }

  if (weekendPrice > 0 && weekendPrice < basePrice) {
    showFieldError('[name="weekend_price"]', "Weekend price should be higher than base price")
    return false
  }

  if (seasonPrice > 0 && seasonPrice < basePrice) {
    showFieldError('[name="season_price"]', "Seasonal price should be higher than base price")
    return false
  }

  return true
}

function validateImages() {
  if (uploadedImages.length === 0) {
    showNotification("Please upload at least one room image.", "error")
    return false
  }
  return true
}

function validateField(event) {
  const field = event.target
  const value = field.value.trim()
  let isValid = true

  // Clear previous error
  clearError(event)

  // Required field validation
  if (field.hasAttribute("required") && !value) {
    showFieldError(field, "This field is required")
    isValid = false
  }

  // Type-specific validation
  if (value) {
    switch (field.type) {
      case "email":
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          showFieldError(field, "Please enter a valid email address")
          isValid = false
        }
        break
      case "number":
        if (isNaN(value) || Number.parseFloat(value) < 0) {
          showFieldError(field, "Please enter a valid positive number")
          isValid = false
        }
        break
    }
  }

  return isValid
}

function showFieldError(field, message) {
  if (typeof field === "string") {
    field = document.querySelector(field)
  }

  field.classList.add("error")
  const errorElement = field.parentNode.querySelector(".error-message")
  if (errorElement) {
    errorElement.textContent = message
  }
}

function clearError(event) {
  const field = event.target
  field.classList.remove("error")
  const errorElement = field.parentNode.querySelector(".error-message")
  if (errorElement) {
    errorElement.textContent = ""
  }
}

function saveCurrentStageData() {
  const currentStageElement = document.getElementById(`stage-${currentStage}`)
  const inputs = currentStageElement.querySelectorAll("input, select, textarea")

  inputs.forEach((input) => {
    if (input.type === "checkbox") {
      if (!formData.amenities) formData.amenities = []
      if (input.checked && !formData.amenities.includes(input.value)) {
        formData.amenities.push(input.value)
      } else if (!input.checked) {
        formData.amenities = formData.amenities.filter((id) => id !== input.value)
      }
    } else {
      formData[input.name] = input.value
    }
  })
}

function setupPricingPreview() {
  const basePrice = document.querySelector('[name="price_per_night"]')
  const weekendPrice = document.querySelector('[name="weekend_price"]')
  const seasonPrice = document.querySelector('[name="season_price"]')

  function updatePreview() {
    document.getElementById("regular-preview").textContent = "$" + (Number.parseFloat(basePrice.value) || 0).toFixed(2)
    document.getElementById("weekend-preview").textContent =
      "$" + (Number.parseFloat(weekendPrice.value) || Number.parseFloat(basePrice.value) || 0).toFixed(2)
    document.getElementById("seasonal-preview").textContent =
      "$" + (Number.parseFloat(seasonPrice.value) || Number.parseFloat(basePrice.value) || 0).toFixed(2)
  }
  ;[basePrice, weekendPrice, seasonPrice].forEach((input) => {
    input.addEventListener("input", updatePreview)
  })
}

function setupAmenitiesTracking() {
  const checkboxes = document.querySelectorAll('[name="amenities[]"]')
  const countElement = document.getElementById("amenity-count")
  const listElement = document.getElementById("selected-list")

  function updateAmenities() {
    const selected = Array.from(checkboxes).filter((cb) => cb.checked)
    countElement.textContent = selected.length

    listElement.innerHTML = ""
    selected.forEach((checkbox) => {
      const label = checkbox.parentNode.querySelector("span").textContent
      const tag = document.createElement("div")
      tag.className = "selected-tag"
      tag.innerHTML = `
                ${label}
                <span class="remove" onclick="removeAmenity('${checkbox.value}')">&times;</span>
            `
      listElement.appendChild(tag)
    })
  }

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateAmenities)
  })

  // Make removeAmenity globally available
  window.removeAmenity = (value) => {
    const checkbox = document.querySelector(`[name="amenities[]"][value="${value}"]`)
    if (checkbox) {
      checkbox.checked = false
      updateAmenities()
    }
  }
}

function setupDescriptionCounter() {
  const textarea = document.querySelector('[name="description"]')
  const counter = document.getElementById("char-count")

  textarea.addEventListener("input", function () {
    const count = this.value.length
    counter.textContent = count

    if (count > 500) {
      counter.style.color = "#e74c3c"
      this.value = this.value.substring(0, 500)
      counter.textContent = 500
    } else {
      counter.style.color = "#666"
    }
  })
}

function setupImageUpload() {
  const uploadZone = document.getElementById("uploadZone")
  const imageInput = document.getElementById("imageInput")
  const previewContainer = document.getElementById("imagePreview")

  // Click to upload
  uploadZone.addEventListener("click", () => imageInput.click())

  // Drag and drop
  uploadZone.addEventListener("dragover", (e) => {
    e.preventDefault()
    uploadZone.classList.add("dragover")
  })

  uploadZone.addEventListener("dragleave", () => {
    uploadZone.classList.remove("dragover")
  })

  uploadZone.addEventListener("drop", (e) => {
    e.preventDefault()
    uploadZone.classList.remove("dragover")
    handleFiles(e.dataTransfer.files)
  })

  // File input change
  imageInput.addEventListener("change", (e) => {
    handleFiles(e.target.files)
  })

  function handleFiles(files) {
    Array.from(files).forEach((file) => {
      if (uploadedImages.length >= 5) {
        showNotification("Maximum 5 images allowed", "error")
        return
      }

      if (!file.type.startsWith("image/")) {
        showNotification("Please upload only image files", "error")
        return
      }

      if (file.size > 5 * 1024 * 1024) {
        showNotification("File size must be less than 5MB", "error")
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        const imageData = {
          file: file,
          url: e.target.result,
          id: Date.now() + Math.random(),
        }

        uploadedImages.push(imageData)
        displayImagePreview(imageData)
      }
      reader.readAsDataURL(file)
    })
  }

  function displayImagePreview(imageData) {
    const preview = document.createElement("div")
    preview.className = "image-preview"
    preview.innerHTML = `
            <img src="${imageData.url}" alt="Room image">
            <button type="button" class="image-remove" onclick="removeImage('${imageData.id}')">
                <i class="fas fa-times"></i>
            </button>
        `
    previewContainer.appendChild(preview)
  }

  // Make removeImage globally available
  window.removeImage = (id) => {
    uploadedImages = uploadedImages.filter((img) => img.id != id)
    const previews = previewContainer.querySelectorAll(".image-preview")
    previews.forEach((preview) => {
      const button = preview.querySelector(".image-remove")
      if (button.onclick.toString().includes(id)) {
        preview.remove()
      }
    })
  }
}

function handleSubmit(event) {
  event.preventDefault()

  if (!validateCurrentStage()) {
    return
  }

  saveCurrentStageData()

  // Show loading state
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Room...'
  submitBtn.disabled = true

  // Simulate API call
  setTimeout(() => {
    showSuccessModal()
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Create Room'
    submitBtn.disabled = false
  }, 2000)
}

function saveAsDraft() {
  saveCurrentStageData()
  showNotification("Draft saved successfully!", "success")

  // Here you would typically save to localStorage or send to server
  localStorage.setItem(
    "roomDraft",
    JSON.stringify({
      formData,
      currentStage,
      uploadedImages: uploadedImages.map((img) => ({ ...img, file: null })), // Can't store File objects
    }),
  )
}

function showSuccessModal() {
  const modal = document.getElementById("successModal")
  const summary = document.getElementById("roomSummary")

  // Populate room summary
  summary.innerHTML = `
        <div><strong>Room Number:</strong> ${formData.room_number || "N/A"}</div>
        <div><strong>Type:</strong> ${getSelectedText('[name="room_type_id"]') || "N/A"}</div>
        <div><strong>Floor:</strong> ${getSelectedText('[name="floor_number"]') || "N/A"}</div>
        <div><strong>Capacity:</strong> ${formData.capacity || "N/A"} guests</div>
        <div><strong>Base Price:</strong> $${formData.price_per_night || "0"}</div>
        <div><strong>Amenities:</strong> ${formData.amenities ? formData.amenities.length : 0} selected</div>
        <div><strong>Images:</strong> ${uploadedImages.length} uploaded</div>
    `

  modal.classList.add("show")
}

function getSelectedText(selector) {
  const select = document.querySelector(selector)
  return select ? select.options[select.selectedIndex]?.text : ""
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-triangle" : "info-circle"}"></i>
        ${message}
    `

  // Add styles
  notification.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        background: ${type === "success" ? "#27ae60" : type === "error" ? "#e74c3c" : "#3498db"};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 300px;
    `

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  // Remove after 4 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (document.body.contains(notification)) {
        document.body.removeChild(notification)
      }
    }, 300)
  }, 4000)
}

// Modal functions
function createAnother() {
  document.getElementById("successModal").classList.remove("show")

  // Reset form
  form.reset()
  formData = {}
  uploadedImages = []
  currentStage = 1

  // Clear image previews
  document.getElementById("imagePreview").innerHTML = ""

  // Reset progress
  showStage(1)

  showNotification("Form reset. Ready for new room!", "success")
}

function viewRooms() {
  document.getElementById("successModal").classList.remove("show")
  showNotification("Redirecting to rooms list...", "info")

  // Here you would typically redirect to the rooms page
  setTimeout(() => {
    window.location.href = "rooms.html"
  }, 1000)
}

// Keyboard navigation
document.addEventListener("keydown", (e) => {
  // Enter key to proceed to next stage
  if (e.key === "Enter" && e.ctrlKey) {
    e.preventDefault()
    if (currentStage < totalStages) {
      handleNext()
    } else {
      handleSubmit(e)
    }
  }

  // Escape key to go back
  if (e.key === "Escape") {
    e.preventDefault()
    if (currentStage > 1) {
      handlePrevious()
    }
  }

  // Arrow keys for navigation
  if (e.key === "ArrowRight" && e.altKey) {
    e.preventDefault()
    if (currentStage < totalStages) {
      handleNext()
    }
  }

  if (e.key === "ArrowLeft" && e.altKey) {
    e.preventDefault()
    if (currentStage > 1) {
      handlePrevious()
    }
  }
})

// Auto-save functionality
setInterval(() => {
  if (Object.keys(formData).length > 0) {
    saveAsDraft()
  }
}, 30000) // Auto-save every 30 seconds

// Load draft on page load
window.addEventListener("load", () => {
  const draft = localStorage.getItem("roomDraft")
  if (draft) {
    try {
      const draftData = JSON.parse(draft)

      // Ask user if they want to restore draft
      if (confirm("Found a saved draft. Would you like to continue where you left off?")) {
        formData = draftData.formData
        currentStage = draftData.currentStage

        // Populate form fields
        Object.keys(formData).forEach((key) => {
          const field = document.querySelector(`[name="${key}"]`)
          if (field) {
            if (field.type === "checkbox") {
              field.checked = formData.amenities && formData.amenities.includes(field.value)
            } else {
              field.value = formData[key]
            }
          }
        })

        // Show the saved stage
        showStage(currentStage)

        // Update pricing preview if on pricing stage
        if (currentStage === 2) {
          document.querySelector('[name="price_per_night"]').dispatchEvent(new Event("input"))
        }

        // Update amenities if on amenities stage
        if (currentStage === 3) {
          document.querySelector('[name="amenities[]"]').dispatchEvent(new Event("change"))
        }

        showNotification("Draft restored successfully!", "success")
      } else {
        localStorage.removeItem("roomDraft")
      }
    } catch (error) {
      console.error("Error loading draft:", error)
      localStorage.removeItem("roomDraft")
    }
  }
})

// Form validation on stage change
function validateStageTransition(fromStage, toStage) {
  // Additional validation logic for specific stage transitions
  if (fromStage === 2 && toStage === 3) {
    // Ensure pricing is reasonable before moving to amenities
    const basePrice = Number.parseFloat(formData.price_per_night)
    if (basePrice > 10000) {
      return confirm("The base price seems very high. Are you sure you want to continue?")
    }
  }

  return true
}

// Responsive handling
function handleResize() {
  if (window.innerWidth <= 768) {
    document.getElementById("sidebar").classList.add("collapsed")
    document.querySelector(".main-content").classList.add("expanded")
  } else {
    document.getElementById("sidebar").classList.remove("collapsed")
    document.querySelector(".main-content").classList.remove("expanded")
  }
}

window.addEventListener("resize", handleResize)
handleResize() // Call on initial load

// Close modal when clicking outside
document.getElementById("successModal").addEventListener("click", function (e) {
  if (e.target === this) {
    this.classList.remove("show")
  }
})

console.log("Hotel Room Management System - Multi-stage Form Initialized")
