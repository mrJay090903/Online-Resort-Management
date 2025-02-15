<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Casita De Grands</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">
  <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
  /* Ensure the imported font applies correctly */
  .font-winter-story {
    font-family: 'Winter Story', sans-serif;
  }

  .bg-pattern {
    background-color: #ffffff;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }

  .pattern-grid {
    background-image:
      linear-gradient(to right, rgba(0, 0, 0, 0.05) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
    background-size: 20px 20px;
  }

  .animate-fade-in {
    animation: fadeIn 1s ease-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  </style>
</head>

<body class="bg-pattern min-h-screen">
  <?php include('components/nav.php'); ?>

  <div class="pt-20 pb-12">
    <!-- Hero Section -->
    <div class="bg-gradient-to-b from-emerald-50 to-white py-20 mb-12 relative overflow-hidden">
      <div class="absolute inset-0 bg-emerald-50 opacity-50 pattern-grid"></div>
      <div class="max-w-6xl mx-auto px-6 relative">
        <div class="text-center">
          <h1 class="text-6xl font-winter-story text-gray-800 mb-6 animate-fade-in">
            Welcome to<br />
            <span class="text-emerald-600">Casita De Grands</span>
          </h1>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto font-[Raleway] leading-relaxed">
            Your perfect getaway in the heart of Guinobatan, Albay.<br />
            Experience tranquility and comfort like never before.
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-6">
      <!-- About Section -->
      <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-winter-story text-gray-800 mb-6">Our Story</h2>
            <p class="text-gray-700 text-lg leading-relaxed font-[Raleway] mb-6">
              Casita de Grands Resort is a peaceful getaway nestled in the heart of Guinobatan, Albay.
              Surrounded by lush greenery, it's a perfect place to relax and enjoy nature. We offer cozy
              accommodations, a family-friendly pool, and delicious home-style meals.
            </p>
            <p class="text-gray-700 text-lg leading-relaxed font-[Raleway]">
              Our venue is ideal for special events like weddings and gatherings, providing a scenic backdrop
              for unforgettable moments. At Casita de Grands, we aim to make every visit a blend of comfort,
              beauty, and heartfelt hospitality.
            </p>
          </div>
          <div class="border border-gray-300 rounded-lg overflow-hidden shadow-lg h-[400px]">
            <iframe class="w-full h-full"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3196.0938282885913!2d123.61211257410173!3d13.247687487093595!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a1a76ad0ad6ce7%3A0xfd5f5bd98cec3df7!2sCasita%20De%20Grands!5e1!3m2!1sen!2sph!4v1739272226459!5m2!1sen!2sph"
              allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>
      </div>

      <!-- Features Section -->
      <div class="grid md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
          <div class="text-emerald-500 text-4xl mb-4">🏊‍♂️</div>
          <h3 class="text-xl font-semibold mb-2">Swimming Pool</h3>
          <p class="text-gray-600">Enjoy our refreshing pool perfect for both adults and children.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
          <div class="text-emerald-500 text-4xl mb-4">🏡</div>
          <h3 class="text-xl font-semibold mb-2">Cozy Accommodations</h3>
          <p class="text-gray-600">Comfortable rooms designed for your relaxation and peace of mind.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
          <div class="text-emerald-500 text-4xl mb-4">🎉</div>
          <h3 class="text-xl font-semibold mb-2">Event Venue</h3>
          <p class="text-gray-600">Perfect setting for weddings, celebrations, and corporate events.</p>
        </div>
      </div>

      <!-- Feedback Button -->
      <!--
      <div class="mt-12">
        <button onclick="showFeedbackModal()" 
                class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
            <span class="mr-2">✨</span>
            Share Your Experience
        </button>
      </div>
      -->
    </div>
  </div>

  <!-- Developers Section -->
  <footer class="bg-gradient-to-b from-gray-900 to-gray-800 text-white py-20">
    <div class="max-w-6xl mx-auto px-6">
      <h3 class="text-4xl mb-16 font-winter-story text-center text-white">
        Meet Our <span class="text-emerald-400">Team</span>
      </h3>
      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <!-- Team Member 1 -->
        <div class="group">
          <div
            class="bg-white/10 backdrop-blur-sm p-8 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2">
            <div class="text-center">
              <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                JB
              </div>
              <h4 class="text-xl font-semibold mb-2">Jonathan Broqueza</h4>
              <p class="text-emerald-300 mb-4">Frontend Developer</p>
              <div class="flex items-center justify-center gap-2 text-gray-300">
                <span class="bg-white/10 p-2 rounded-lg">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z">
                    </path>
                  </svg>
                </span>
                <span>+63 945 682 1503</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 2 -->
        <div class="group">
          <div
            class="bg-white/10 backdrop-blur-sm p-8 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2">
            <div class="text-center">
              <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                JC
              </div>
              <h4 class="text-xl font-semibold mb-2">Jay-ar Cope</h4>
              <p class="text-emerald-300 mb-4">Lead Developer</p>
              <div class="flex items-center justify-center gap-2 text-gray-300">
                <span class="bg-white/10 p-2 rounded-lg">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z">
                    </path>
                  </svg>
                </span>
                <span>+63 975 920 9976</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 3 -->
        <div class="group">
          <div
            class="bg-white/10 backdrop-blur-sm p-8 rounded-2xl hover:bg-white/20 transition-all duration-300 transform hover:-translate-y-2">
            <div class="text-center">
              <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                MD
              </div>
              <h4 class="text-xl font-semibold mb-2">Michael John Dacillo</h4>
              <p class="text-emerald-300 mb-4">Backend Developer</p>
              <div class="flex items-center justify-center gap-2 text-gray-300">
                <span class="bg-white/10 p-2 rounded-lg">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z">
                    </path>
                  </svg>
                </span>
                <span>+63 995 943 3804</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Feedback Modal -->
  <div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300">
      <div class="p-6">
        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Share Your Feedback</h3>
        <form id="feedbackForm" method="POST" action="../handlers/feedback_handler.php" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
            <div class="flex space-x-4">
              <?php for($i = 1; $i <= 5; $i++): ?>
              <label class="cursor-pointer">
                <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                <svg
                  class="w-8 h-8 star-rating fill-current peer-checked:text-yellow-400 hover:text-yellow-400 text-gray-300 transition-colors duration-200"
                  viewBox="0 0 24 24">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                </svg>
              </label>
              <?php endfor; ?>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
            <textarea name="message" rows="4" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
              placeholder="Tell us about your experience..."></textarea>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeFeedbackModal()"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors duration-200">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors duration-200">
              Submit Feedback
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const modal = document.getElementById('feedbackModal');
    const form = document.getElementById('feedbackForm');
    const stars = document.querySelectorAll('.star-rating');
    const ratingContainer = document.querySelector('.flex.space-x-4');

    // Show modal function
    window.showFeedbackModal = function() {
      modal.classList.remove('hidden');
    }

    // Close modal function
    window.closeFeedbackModal = function() {
      modal.classList.add('hidden');
      form.reset();
      updateStars(-1); // Reset stars
    }

    // Star rating functionality
    stars.forEach((star, index) => {
      // Click handler
      star.parentElement.addEventListener('click', () => {
        const input = star.parentElement.querySelector('input');
        input.checked = true;
        updateStars(index);
      });

      // Hover handler
      star.parentElement.addEventListener('mouseover', () => {
        updateStars(index);
      });
    });

    // Handle mouseout for rating container
    ratingContainer.addEventListener('mouseout', () => {
      const selectedInput = form.querySelector('input[name="rating"]:checked');
      const selectedIndex = selectedInput ? parseInt(selectedInput.value) - 1 : -1;
      updateStars(selectedIndex);
    });

    // Update stars appearance
    function updateStars(activeIndex) {
      stars.forEach((star, index) => {
        if (index <= activeIndex) {
          star.classList.remove('text-gray-300');
          star.classList.add('text-yellow-400');
        } else {
          star.classList.remove('text-yellow-400');
          star.classList.add('text-gray-300');
        }
      });
    }

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeFeedbackModal();
      }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
      const rating = form.querySelector('input[name="rating"]:checked');
      const message = form.querySelector('textarea[name="message"]').value.trim();

      if (!rating) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Please select a rating',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }

      if (!message) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Please enter your feedback message',
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
        return;
      }
    });
  });
  </script>
</body>

</html>