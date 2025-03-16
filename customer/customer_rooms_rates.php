<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Include Navigation -->
    <?php include 'components/nav.php'; ?>

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-center mb-6">Room Rates</h1>
        <div id="rooms-container" class="space-y-6"></div>
    </div>

<script>
    async function fetchRooms() {
        try {
            const response = await fetch("../components/get_rooms.php");
            const roomsData = await response.json();
            renderRooms(roomsData);
        } catch (error) {
            console.error("Error fetching rooms:", error);
        }
    }

    function renderRooms(roomsData) {
        const container = document.getElementById("rooms-container");
        container.innerHTML = ""; 
        roomsData.forEach(room => {
            const roomElement = document.createElement("div");
            roomElement.className = "bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row"; // Flex row for larger screens

            roomElement.innerHTML = `
                <!-- Image (Left Side) -->
                <img src="${room.image}" alt="${room.room_name}" class="w-full md:w-1/3 h-60 object-cover">

                <!-- Details (Right Side) -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <h2 class="text-2xl font-semibold">${room.room_name}</h2>
                    <p class="text-lg text-gray-500">${room.description}</p>
                    <p class="text-xl text-gray-600 font-bold">₱${room.price}</p>
                    <p class="mt-2"><strong>Capacity:</strong> ${room.capacity} pax</p>
                    <p class="mt-2"><strong>Base Price:</strong> ₱${room.base_price}</p>
                    <p class="mt-2"><strong>Day Price:</strong> ₱${room.day_price}</p>
                    <p class="mt-2"><strong>Night Price:</strong> ₱${room.night_price}</p>
                    <ul class="mt-2 list-disc list-inside text-gray-700">
                        ${room.inclusions.map(item => `<li>${item}</li>`).join("")}
                    </ul>
                    <p class="mt-2 text-sm text-gray-500">* Status: ${room.status}</p>

                    <!-- Reserve Button (Aligned Bottom Left) -->
                    <div class="mt-4 flex justify-end">
                        <button class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
                            Reserve Now
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(roomElement);
        });
    }

    document.addEventListener("DOMContentLoaded", fetchRooms);
</script>

<?php include 'components/footer.php'; ?>

</body>
</html>
