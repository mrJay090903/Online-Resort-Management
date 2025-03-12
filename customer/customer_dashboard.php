<?php
// Start session at the very beginning of the file, before any output
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

// Handle Login
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Debug log
    error_log("Login attempt - Email: " . $email . ", Password: " . $password);
    
    // Query to check user credentials
    $sql = "SELECT u.id, u.email, u.password, u.user_type,
            CASE 
                WHEN u.user_type = 'customer' THEN c.full_name
                WHEN u.user_type = 'staff' THEN s.staff_name
                WHEN u.user_type = 'admin' THEN u.email
            END as name
            FROM users u
            LEFT JOIN customers c ON u.id = c.user_id AND u.user_type = 'customer'
            LEFT JOIN staff s ON u.id = s.user_id AND u.user_type = 'staff'
            WHERE u.email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For testing: If it's admin and password is admin123, allow login
            if ($user['user_type'] === 'admin' && $password === 'admin123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                header('Location: admin/dashboard.php');
                exit();
            }
            // For regular users, use password verification
            else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                
                switch($_SESSION['user_type']) {
                    case 'customer':
                        header('Location: customer/customer_dashboard.php');
                        exit();
                    case 'staff':
                        header('Location: staff/staff_dashboard.php');
                        exit();
                }
            } else {
                $_SESSION['error'] = "Invalid password";
            }
        } else {
            $_SESSION['error'] = "User not found";
        }
        $stmt->close();
    } else {
        error_log("SQL preparation failed: " . $conn->error);
        $_SESSION['error'] = "Database error occurred";
    }
}

// Handle Signup
if (isset($_POST['signup'])) {
    $conn->begin_transaction();
    
    try {
        $name = $conn->real_escape_string($_POST['fullName']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];
        $contactNumber = $conn->real_escape_string($_POST['contactNumber']);
        
        // Validate password match
        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match");
        }
        
        // Check if email already exists
        $checkEmail = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($checkEmail)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception("Email already exists");
            }
            $stmt->close();
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into users table
        $sql = "INSERT INTO users (email, password, user_type) VALUES (?, ?, 'customer')";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $email, $hashedPassword);
            $stmt->execute();
            $userId = $conn->insert_id;
            $stmt->close();
            
            // Insert into customers table
            $sql = "INSERT INTO customers (user_id, full_name, contact_number, email) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isss", $userId, $name, $contactNumber, $email);
                $stmt->execute();
                $stmt->close();
                
                $conn->commit();
                $_SESSION['success'] = "Registration successful! Please login.";
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: index.php');
    exit();
}

// Fetch active features
$features_query = "SELECT * FROM features WHERE status = 'active' ORDER BY id";
$features = $conn->query($features_query)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
  <link href="../src/output.css" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans+Georgian:wght@100..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/second-quotes" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Sans:ital,wght@0,100..800;1,100..800&display=swap"
    rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ephesis&display=swap" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
  <!-- Add SweetAlert2 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
  <style>
  .font-winter-story {
    font-family: 'Winter Story', sans-serif;
  }
  </style>
</head>

<body class="font-sans bg-gray-100" x-data>

  <?php include('components/nav.php'); ?>

  <!-- Hero Section -->
  <section class="relative w-full h-[570px] overflow-hidden">
    <video class="absolute inset-0 w-full h-full object-cover brightness-85" autoplay loop muted playsinline>
      <source src="../videos/bg-vid.mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>

    <div class="absolute inset-0 flex flex-col justify-center items-center text-white text-center px-4">
      <p class="font-['Raleway'] text-sm uppercase tracking-widest">Welcome To</p>
      <h1 class="text-6xl font-bold font-['playfair']">CASITA DE GRANDS</h1>
      <p class="mt-2 text-lg">Escape to Tranquility, Your Hidden Paradise Awaits</p>
      <a href="new_booking.php"
        class="w-40 mt-6 px-4 py-2 border-2 border-white text-white bg-transparent bg-opacity-40 backdrop-blur-md hover:bg-white hover:text-black transition-all duration-300">
        Book Now
      </a>
    </div>
  </section>

  <!-- Description Section -->
  <section class="text-center py-16 px-6">
    <h2 class="text-4xl font-['Second_Quotes']">Your Escape to Serenity</h2>
    <p class="mt-10 text-gray-600 max-w-3xl mx-auto font-['Ubuntu_Sans']">
      Looking for a relaxing escape? Casita De Grands, hidden away in the lush greenery of Muladbucad Grande,
      Guinobatan, Albay, is the perfect place to unwind. Just minutes from Guinobatan Centro, our resort offers
      a peaceful retreat surrounded by nature.
    </p>
    <p class="mt-4 text-gray-600 max-w-3xl mx-auto">
      Take a dip in our beautiful infinity pool and immerse yourself in the calming view of lush nature all around.
    </p>
  </section>

  <!-- Carousel Section -->
  <section class="text-center py-16 px-6">
    <h2 class="text-4xl font-['Ephesis'] mb-8">Discover Your Perfect Stay</h2>
    <div class="max-w-6xl mx-auto relative">
      <!-- Carousel Container -->
      <div class="overflow-hidden relative h-[650px] rounded-lg">
        <div id="carousel" class="flex transition-transform duration-500 ease-in-out h-full">
          <div class="flex-shrink-0 w-full">
            <img src="../assets/picture/picture1.jpg" alt="Room 1" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/picture/picture2.png" alt="Room 2" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/picture/picture3.png" alt="Room 3" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/picture/picture4.png" alt="Room 4" class="w-full h-full object-cover">
          </div>
          <div class="flex-shrink-0 w-full">
            <img src="../assets/picture/picture5.png" alt="Room 5" class="w-full h-full object-cover">
          </div>

        </div>

      </div>

      <!-- Navigation Buttons -->
      <button onclick="prevSlide()"
        class="absolute top-1/2 left-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <button onclick="nextSlide()"
        class="absolute top-1/2 right-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Dots Navigation -->
      <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <button onclick="goToSlide(0)"
          class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
        <button onclick="goToSlide(1)"
          class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
        <button onclick="goToSlide(2)"
          class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
        <button onclick="goToSlide(3)"
          class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
        <button onclick="goToSlide(4)"
          class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity duration-200"></button>
      </div>

    </div>

  </section>

  <!-- Feature Section -->
  <section class="bg-gray-100 py-16">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Features</h2>
        <p class="text-gray-600 mb-12 text-lg">Discover the luxurious amenities and breathtaking experiences.</p>

        <!-- Features Carousel -->
        <div class="relative max-w-7xl mx-auto px-4">
            <div class="features-carousel overflow-hidden">
                <div class="swiper-wrapper">
                    <?php foreach ($features as $feature): ?>
                    <div class="swiper-slide p-2">
                        <div class="bg-white rounded-xl shadow-xl overflow-hidden transform transition-all duration-500 hover:scale-[1.03] hover:shadow-2xl">
                            <!-- Larger Image Container -->
                            <div class="relative aspect-[16/12] overflow-hidden">
                                <img src="../uploads/features/<?php echo htmlspecialchars($feature['image_url']); ?>"
                                     alt="<?php echo htmlspecialchars($feature['title']); ?>"
                                     class="w-full h-full object-cover transform transition-transform duration-700 hover:scale-105"
                                     loading="lazy">
                                <!-- Gradient Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                            <!-- Content Section -->
                            <div class="p-6 bg-white">
                                <h3 class="text-2xl font-bold text-gray-800 mb-3 transform transition-all duration-500 animate-fade-in">
                                    <?php echo htmlspecialchars($feature['title']); ?>
                                </h3>
                                <p class="text-gray-600 leading-relaxed transform transition-all duration-500 delay-100 animate-slide-up">
                                    <?php echo htmlspecialchars($feature['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation Buttons -->
                <button class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/90 text-emerald-600 w-12 h-12 rounded-full shadow-lg hover:bg-emerald-600 hover:text-white transition-all duration-300 z-10 prev-button flex items-center justify-center">
                    <i class="fas fa-chevron-left text-xl"></i>
                </button>
                <button class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/90 text-emerald-600 w-12 h-12 rounded-full shadow-lg hover:bg-emerald-600 hover:text-white transition-all duration-300 z-10 next-button flex items-center justify-center">
                    <i class="fas fa-chevron-right text-xl"></i>
                </button>

                <!-- Pagination -->
                <div class="swiper-pagination mt-8"></div>
            </div>
        </div>
    </div>
</section>

  <!-- Customer Feedback Section -->
  <section class="bg-white py-16">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-['Second_Quotes'] text-gray-800 mb-2">Customer's Feedback</h2>
      <p class="text-gray-500 uppercase text-xs tracking-[0.5em] mb-12 font-['Raleway']">Your Opinion Matters</p>

      <?php
        // Check for pending feedback
        $pending_sql = "SELECT f.*, c.full_name 
                       FROM feedbacks f 
                       JOIN customers c ON f.customer_id = c.id 
                       WHERE f.customer_id = (SELECT id FROM customers WHERE user_id = ?) 
                       AND f.status = 'pending'";
        $stmt = $conn->prepare($pending_sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $pending_result = $stmt->get_result();

        if ($pending_result->num_rows > 0) {
            echo '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                    <p class="text-yellow-800">
                        <span class="font-semibold">Thank you for your feedback!</span> 
                        Your feedback is currently pending approval from our administrators.
                    </p>
                  </div>';
        }

        // Fetch all approved feedbacks
        $sql = "SELECT f.*, c.full_name 
                FROM feedbacks f 
                JOIN customers c ON f.customer_id = c.id 
                WHERE f.status = 'approved' 
                ORDER BY f.created_at DESC";
        $result = $conn->query($sql);
        $feedbacks = $result->fetch_all(MYSQLI_ASSOC);
        $total_feedbacks = count($feedbacks);
        ?>

      <?php if ($total_feedbacks > 0): ?>
      <div class="relative overflow-hidden">
        <!-- Carousel Container -->
        <div id="feedbackCarousel" class="relative w-full">
          <div class="flex transition-transform duration-500 ease-in-out">
            <?php
                        // Split feedbacks into groups of 3
                        $feedback_groups = array_chunk($feedbacks, 3);
                        foreach ($feedback_groups as $group):
                        ?>
            <div class="w-full flex-none grid md:grid-cols-3 gap-8">
              <?php foreach ($group as $feedback): ?>
              <div class="bg-gray-100 p-8 rounded-lg shadow-md">
                <!-- Rating Stars -->
                <div class="flex justify-center mb-6">
                  <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 24 24">
                    <path
                      d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                  </svg>
                  <?php endfor; ?>
                </div>

                <!-- Feedback Message -->
                <p class="italic text-gray-600 mb-6">
                  "<?php echo htmlspecialchars($feedback['message']); ?>"
                </p>

                <!-- Customer Name -->
                <h3 class="font-semibold text-gray-800 mb-2">
                  <?php echo htmlspecialchars($feedback['full_name']); ?>
                </h3>

                <!-- Feedback Date -->
                <p class="text-sm text-gray-500">
                  <?php echo date('F d, Y', strtotime($feedback['created_at'])); ?>
                </p>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($total_feedbacks > 3): ?>
        <!-- Navigation Arrows -->
        <button onclick="prevFeedbackSlide()"
          class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-all -ml-4">
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button onclick="nextFeedbackSlide()"
          class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white transition-all -mr-4">
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Dots Navigation -->
        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 flex space-x-2 mb-4">
          <?php for ($i = 0; $i < ceil($total_feedbacks / 3); $i++): ?>
          <button onclick="goToFeedbackSlide(<?php echo $i; ?>)"
            class="w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors duration-200">
          </button>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <!-- Fallback content if no feedbacks -->
      <div class="text-center text-gray-500">
        <p>No approved customer feedbacks yet. Share your experience and be the first!</p>
      </div>
      <?php endif; ?>

      <!-- Share Feedback Button -->
      <div class="mt-12">
        <button onclick="openFeedbackModal()"
          class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
          <span class="mr-2">✨</span>
          Share Your Experience
        </button>
      </div>
    </div>
  </section>

  <!-- Book Now Button -->
  <section class="bg-gray-900 text-white py-16 text-center">
    <h2 class="text-4xl font-['Second_Quotes'] mb-4">Ready to Book Your Stay?</h2>
    <p class="text-gray-400 mb-8">Experience luxury and comfort at Casita De Grands</p>
    <a href="reservations.php"
      class="inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
      <span class="mr-2">📅</span>
      Make a Reservation
    </a>
  </section>

  <!-- Footer -->
  <?php include('components/footer.php'); ?>

  <!-- Feedback Modal -->
  <div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center pb-3">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Share Your Experience</h3>
          <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form id="feedbackForm" class="mt-2">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Rating</label>
            <div class="flex items-center space-x-1">
              <?php for($i = 1; $i <= 5; $i++): ?>
              <button type="button"
                class="rating-star p-1 text-3xl transition-transform duration-200 ease-in-out hover:scale-110 focus:outline-none"
                data-value="<?php echo $i; ?>">
                <span class="text-gray-300">★</span>
              </button>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="0" required>
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Your Message</label>
            <textarea name="message" rows="4" required
              class="shadow-sm focus:ring-emerald-500 focus:border-emerald-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
              placeholder="Tell us about your experience..."></textarea>
          </div>

          <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeFeedbackModal()"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
              Cancel
            </button>
            <button type="submit"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
              Submit Feedback
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  // Feedback Modal Functions
  function openFeedbackModal() {
    document.getElementById('feedbackModal').classList.remove('hidden');
  }

  function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
    resetStars();
  }

  // Star rating functionality
  const stars = document.querySelectorAll('.rating-star');
  let currentRating = 0;

  function updateStars(count, hover = false) {
    stars.forEach((star, index) => {
      const starSpan = star.querySelector('span');
      if (index < count) {
        starSpan.classList.remove('text-gray-300');
        starSpan.classList.add('text-yellow-400');
      } else {
        starSpan.classList.remove('text-yellow-400');
        starSpan.classList.add('text-gray-300');
      }
    });

    if (!hover) {
      currentRating = count;
      document.getElementById('ratingInput').value = count;
    }
  }

  stars.forEach((star, index) => {
    // Click handler
    star.addEventListener('click', () => {
      updateStars(index + 1);
    });

    // Hover handlers
    star.addEventListener('mouseenter', () => {
      updateStars(index + 1, true);
    });

    star.addEventListener('mouseleave', () => {
      updateStars(currentRating, true);
    });
  });

  // Reset stars when closing modal
  function resetStars() {
    currentRating = 0;
    updateStars(0);
  }

  // Handle feedback form submission
  document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (currentRating === 0) {
      Swal.fire({
        icon: 'error',
        title: 'Please select a rating',
        confirmButtonColor: '#059669'
      });
      return;
    }

    const formData = new FormData(this);

    fetch('../handlers/feedback_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Thank you!',
            text: data.message,
            confirmButtonColor: '#059669'
          }).then(() => {
            closeFeedbackModal();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonColor: '#059669'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred. Please try again.',
          confirmButtonColor: '#059669'
        });
      });
  });

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('feedbackModal');
    if (event.target === modal) {
      closeFeedbackModal();
    }
  }
  </script>

  <script>
  let currentIndex = 0;
  const carousel = document.getElementById('carousel');
  const totalSlides = document.querySelectorAll('#carousel > div').length;
  const dots = document.querySelectorAll('.absolute.bottom-4 button');

  function updateCarousel() {
    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    // Update dots
    dots.forEach((dot, index) => {
      dot.style.opacity = index === currentIndex ? '1' : '0.5';
    });
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateCarousel();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateCarousel();
  }

  function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
  }

  // Auto-advance slides every 5 seconds
  let slideInterval = setInterval(nextSlide, 5000);

  // Pause auto-advance on hover
  carousel.parentElement.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
  });

  // Resume auto-advance when mouse leaves
  carousel.parentElement.addEventListener('mouseleave', () => {
    slideInterval = setInterval(nextSlide, 5000);
  });

  // Initial update
  updateCarousel();
  </script>

  <script>
  let currentFeedbackSlide = 0;
  const feedbackCarousel = document.getElementById('feedbackCarousel');
  const totalFeedbackSlides = <?php echo ceil($total_feedbacks / 3); ?>;

  function updateFeedbackCarousel() {
    const translateX = currentFeedbackSlide * -100;
    feedbackCarousel.querySelector('.flex').style.transform = `translateX(${translateX}%)`;

    // Update dots
    document.querySelectorAll('.bottom-0 button').forEach((dot, index) => {
      dot.classList.toggle('bg-gray-400', index === currentFeedbackSlide);
      dot.classList.toggle('bg-gray-300', index !== currentFeedbackSlide);
    });
  }

  function nextFeedbackSlide() {
    currentFeedbackSlide = (currentFeedbackSlide + 1) % totalFeedbackSlides;
    updateFeedbackCarousel();
  }

  function prevFeedbackSlide() {
    currentFeedbackSlide = (currentFeedbackSlide - 1 + totalFeedbackSlides) % totalFeedbackSlides;
    updateFeedbackCarousel();
  }

  function goToFeedbackSlide(index) {
    currentFeedbackSlide = index;
    updateFeedbackCarousel();
  }

  // Auto-advance slides every 5 seconds
  let feedbackInterval = setInterval(nextFeedbackSlide, 5000);

  // Pause auto-advance on hover
  feedbackCarousel.addEventListener('mouseenter', () => {
    clearInterval(feedbackInterval);
  });

  // Resume auto-advance when mouse leaves
  feedbackCarousel.addEventListener('mouseleave', () => {
    feedbackInterval = setInterval(nextFeedbackSlide, 5000);
  });

  // Initial update
  updateFeedbackCarousel();
  </script>

  <script>
  document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    let hasSelection = false;
    let totalGuests = 0;
    const maxGuests = parseInt(formData.get('guests'));

    // Check room selections
    document.querySelectorAll('input[type="number"]').forEach(input => {
      if (input.name.includes('room') && parseInt(input.value) > 0) {
        hasSelection = true;
        // Calculate total guests based on room capacity
        const roomCapacity = parseInt(input.closest('.bg-gray-50').querySelector('.mb-2').textContent.match(
          /\d+/)[0]);
        totalGuests += roomCapacity * parseInt(input.value);
      }
    });

    // Check venue selections
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
      hasSelection = true;
    });

    if (!hasSelection) {
      Swal.fire({
        icon: 'error',
        title: 'No Selection',
        text: 'Please select at least one room or venue',
        confirmButtonColor: '#059669'
      });
      return;
    }

    if (totalGuests < maxGuests && !document.querySelector('input[type="checkbox"]:checked')) {
      Swal.fire({
        icon: 'error',
        title: 'Insufficient Capacity',
        text: 'The selected rooms cannot accommodate all guests. Please select more rooms.',
        confirmButtonColor: '#059669'
      });
      return;
    }

    // If validation passes, show confirmation dialog
    Swal.fire({
      title: 'Confirm Booking',
      text: 'Would you like to proceed with this booking?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#059669',
      cancelButtonColor: '#dc2626',
      confirmButtonText: 'Yes, proceed'
    }).then((result) => {
      if (result.isConfirmed) {
        // Submit booking
        fetch('../handlers/booking_handler.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Booking Submitted!',
                text: data.message,
                confirmButtonColor: '#059669'
              }).then(() => {
                window.location.href = 'bookings.php'; // Redirect to bookings page
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Booking Failed',
                text: data.message,
                confirmButtonColor: '#059669'
              });
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An error occurred. Please try again.',
              confirmButtonColor: '#059669'
            });
          });
      }
    });

    // Date validation
    document.querySelector('input[name="check_in"]').addEventListener('change', function() {
      const checkOutInput = document.querySelector('input[name="check_out"]');
      checkOutInput.min = this.value;
      if (checkOutInput.value && checkOutInput.value <= this.value) {
        checkOutInput.value = '';
      }
    });
  });
  </script>

  <!-- Add this before closing body tag -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
  <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const featuresSwiper = new Swiper('.features-carousel', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      speed: 800,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      effect: 'slide', // Changed from coverflow for better visibility
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
        dynamicBullets: true,
      },
      navigation: {
        nextEl: '.next-button',
        prevEl: '.prev-button',
      },
      breakpoints: {
        640: {
          slidesPerView: 1,
        },
        768: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 32,
        },
      },
    });
  });
  </script>

  <style>
  /* Updated carousel styling */
  .features-carousel {
    padding: 20px 0;
  }

  .swiper-slide {
    height: auto;
    opacity: 1; /* Changed from 0 to make slides visible immediately */
    transform: none; /* Removed initial transform */
  }

  .features-carousel .swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    background: #10B981;
    opacity: 0.5;
    transition: all 0.3s ease;
  }

  .features-carousel .swiper-pagination-bullet-active {
    opacity: 1;
    background: #059669;
    transform: scale(1.2);
  }

  /* Navigation buttons always visible */
  .prev-button,
  .next-button {
    opacity: 1;
    transform: none;
    transition: all 0.3s ease;
  }

  .prev-button:hover,
  .next-button:hover {
    transform: scale(1.1);
  }

  /* Improved card shadows */
  .shadow-xl {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  }

  .hover\:shadow-2xl:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  /* Text animations */
  @keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
  }

  @keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
  }

  .animate-fade-in {
    opacity: 0;
    animation: fadeIn 0.6s ease-out forwards;
  }

  .animate-slide-up {
    opacity: 0;
    animation: slideUp 0.6s ease-out forwards;
  }

  /* Add animation delay for staggered effect */
  .delay-100 {
    animation-delay: 100ms;
  }

  /* Add hover animations */
  .swiper-slide:hover .animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
  }

  .swiper-slide:hover .animate-slide-up {
    animation: slideUp 0.4s ease-out forwards;
  }

  /* Make sure text is visible after animation */
  .swiper-slide.swiper-slide-active .animate-fade-in,
  .swiper-slide.swiper-slide-active .animate-slide-up {
    opacity: 1;
  }

  /* Add smooth transition for text on hover */
  .swiper-slide h3,
  .swiper-slide p {
    transition: transform 0.3s ease;
  }

  .swiper-slide:hover h3 {
    transform: translateY(-2px);
  }

  .swiper-slide:hover p {
    transform: translateY(-2px);
  }
  </style>

</body>

</html>