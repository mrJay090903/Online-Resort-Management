
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Casita de Grands</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/winter-story" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include 'navbar.php'; ?>

    <section class="max-w-6xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-semibold text-gray-800 mb-4 font-['Winter_story']">About Us</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <p class="text-gray-700 text-lg leading-relaxed font-[Raleway]">
                    Casita de Grands Resort is a peaceful getaway nestled in the heart of Guinobatan, Albay. 
                    Surrounded by lush greenery, it’s a perfect place to relax and enjoy nature. We offer cozy 
                    accommodations, a family-friendly pool, and delicious home-style meals. Our venue is ideal for 
                    special events like weddings and gatherings, providing a scenic backdrop for unforgettable moments. 
                    At Casita de Grands, we aim to make every visit a blend of comfort, beauty, and heartfelt hospitality.
                </p>
            </div>
            <div class="border border-gray-300 rounded-lg overflow-hidden shadow-lg w-120 h-60 mb-4 ml-14">
                <iframe
                    class="w-full h-full"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3196.0938282885913!2d123.61211257410173!3d13.247687487093595!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a1a76ad0ad6ce7%3A0xfd5f5bd98cec3df7!2sCasita%20De%20Grands!5e1!3m2!1sen!2sph!4v1739272226459!5m2!1sen!2sph"
                    allowfullscreen=""
                    loading="lazy"
                ></iframe>
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <button class="px-6 py-3 transition-colors duration-300 hover:bg-[#3b3b3b] hover:text-white border-2">
                Give Us Your Feedback
            </button>
        </div>
    </section>
    <footer class="bg-gray-800 text-white py-16">
        <div class="max-w-6xl mx-auto text-center">
            <h3 class="text-xl">Developers</h3>
            <div class="grid md:grid-cols-3 gap-6 mt-4">
                <div>
                    <p class="text-sm">Jonathan Broqueza</p>
                    <p>&#128222; +63 945 682 1503</p>
                </div>
                <div>
                    <p class="text-sm">Jay-ar Cope</p>
                    <p>&#128222; +63 975 920 9976</p>
                </div>
                <div>
                    <p class="text-sm">Michael John Dacillo</p>
                    <p>&#128222; +63 995 943 3804</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
