<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casita De Grands</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script>
        function toggleModal() {
            document.getElementById('loginModal').classList.toggle('hidden');
        }

        function toggleSignupModal() {
            document.getElementById('signupModal').classList.toggle('hidden');
            document.getElementById('loginModal').classList.add('hidden');
        }

        function switchToLogin() {
            toggleSignupModal(); 
            toggleModal(); 
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans+Georgian:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/second-quotes" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Sans:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">        
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
</head>

<body class="font-sans">

<!-- Navbar -->
<nav class="flex justify-between items-center p-2 bg-white shadow-xl sticky top-0 w-full bg-white z-50">
        <div class="flex items-center">
            <img src="videos/casitalogo-removebg-preview.png" alt="Casita De Grands" class="h-13">
        </div>

        <ul class="flex space-x-12 ml-4 -mr-120">
            <li class="relative group">
                <a href="#" class="text-black hover:text-gray-600">
                    Home
                    <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-black group-hover:w-full transition-all duration-300"></span>
                </a>
            </li>
            <li class="relative group">
                <a href="#" class="text-black hover:text-gray-600">
                    Rooms
                    <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-black group-hover:w-full transition-all duration-300"></span>
                </a>
            </li>
            <li class="relative group">
                <a href="#" class="text-black hover:text-gray-600">
                    Reservations
                    <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-black group-hover:w-full transition-all duration-300"></span>
                </a>
            </li>
            <li class="relative group">
                <a href="#" class="text-black hover:text-gray-600">
                    Features
                    <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-black group-hover:w-full transition-all duration-300"></span>
                </a>
            </li>
            <li class="relative group">
                <a href="#" class="text-black hover:text-gray-600">
                    About us
                    <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-black group-hover:w-full transition-all duration-300"></span>
                </a>
            </li>
        </ul>
        <button onclick="toggleModal()" class="font-['Lexend'] border px-6 py-1.5 transition-colors duration-300 hover:bg-[#3b3b3b] hover:text-white">
            LOGIN
        </button>
    </nav>

    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96 relative">
            <button onclick="toggleModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
            <h2 class="text-2xl font-bold text-center mb-4">Sign In</h2>

            <form class="flex flex-col items-center">

            <!-- Email -->
                <div class="mb-4 w-80 mx-auto">
                    <label for="email" class="block text-sm text-gray-600">Email</label>
                    <input type="text" id="email" name="email" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                    outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1" required placeholder="Enter your email">
                </div>

            <!-- Password -->
                <div class="mb-4 w-80 mx-auto">
                    <label for="password" class="block text-sm text-gray-600">Password</label>
                    <input type="password" id="password" name="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700
                    outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1" required placeholder="Enter your password">
                </div>

            <!-- Forgot Password -->
                <p class="text-sm text-[#00B58B] font-semibold hover:underline cursor-pointer mb-4 w-80 mx-auto text-left">
                    <a href="#">Forgot your password?</a>
                </p>
                <button type="submit" class="w-40 px-4 py-2 bg-[#242424] text-white transition-transform transform hover:scale-105 hover:bg-gray-700">
                    SIGN IN
                </button>
                <p class="mt-4 text-sm">Don't have an account? <span onclick="toggleSignupModal()" class="text-[#00B58B] cursor-pointer font-semibold hover:underline">Sign up</span></p>
            </form>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-white p-6 rounded-lg shadow-xl w-100 relative">
            <button onclick="toggleSignupModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>

            <h2 class="text-3xl font-bold font-['Noto_Sans_Georgian'] mb-4 text-center">Sign Up</h2>
            
            <form class="flex flex-col items-center">
                <!-- Full Name -->
                <div class="mb-4 w-84 mx-auto mt-4">
                    <input type="text" id="fullName" name="fullName" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                        outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
                        required placeholder="Fullname">
                </div>

                <!-- Contact Number -->
                <div class="mb-4 w-84 mx-auto">
                    <input type="text" id="contactNumber" name="contactNumber" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                        outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1" 
                        required placeholder="Contact Number">
                </div>

                <!-- Email -->
                <div class="mb-4 w-84 mx-auto">
                    <input type="email" id="email" name="email" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                        outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
                        required placeholder="Email">
                </div>

                <!-- Password -->
                <div class="mb-4 w-84 mx-auto">
                    <input type="password" id="password" name="password" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                        outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
                        required placeholder="Password">
                </div>

                <!-- Confirm Password -->
                <div class="mb-6 w-84 mx-auto">
                    <input type="password" id="confirmPassword" name="confirmPassword" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700 
                        outline-none transition-all duration-300 focus:border-[#00254a] focus:ring-1"
                        required placeholder="Confirm Password">
                </div>

                <!-- Signup Button -->
                <button type="submit" class="w-40 px-4 py-2 bg-[#242424] text-white transition-transform transform hover:scale-105 hover:bg-gray-700">
                    SIGN UP
                </button>

                <!-- Switch to Login -->
                <p class="mt-4 text-sm">Already have an account? 
                <span onclick="switchToLogin()" class="text-[#00B58B] font-semibold hover:underline cursor-pointer">Login</span>
                </p>
            </form>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="relative w-full h-[570px] overflow-hidden">
        <video class="absolute inset-0 w-full h-full object-cover brightness-85" autoplay loop muted>
            <source src="videos/bg-vid.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <div class="absolute inset-0 flex flex-col justify-center items-center text-white text-center px-4">
            <p class="font-['Raleway'] text-sm uppercase tracking-widest">Welcome To</p>
            <h1 class="text-6xl font-bold font-['playfair']">CASITA DE GRANDS</h1>
            <p class="mt-2 text-lg">Escape to Tranquility, Your Hidden Paradise Awaits</p>
            <button class="w-40 mt-6 px-4 py-2 border-2 border-white text-white bg-transparent bg-opacity-40 backdrop-blur-md hover:bg-white hover:text-black transition-all duration-300">
            Stay with Us
        </button>

        </div>
    </section>

    <!-- Description Section -->
    <section class="text-center py-25 px-6 mt-2">
        <h2 class="text-4xl font-second-quotes font-['Second_Quotes']">Your Escape to Serenity</h2>
        <p class="mt-10 text-gray-600 max-w-3xl mx-auto font-['Ubuntu_Sans']">
            Looking for a relaxing escape? Casita De Grands, hidden away in the lush greenery of Muladbucad Grande, Guinobatan, Albay, is the perfect place to unwind.
            Just minutes from Guinobatan Centro, our resort offers a peaceful retreat surrounded by nature. Take a dip in our beautiful infinity pool and immerse yourself
            in the calming view of lush nature all around.
        </p>
        <p class="mt-2 text-gray-600">
            We’re located at Purok 7, Muladbucad Grande, Guinobatan, Albay. Come and make unforgettable memories with us!
        </p>
    </section>

</body>
</html>
