<!-- User dropdown -->
<div x-show="open" @click.away="open = false" class="absolute right-0 w-48 mt-2 bg-white rounded-lg shadow-lg py-2">
    <a href="profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
        Profile Settings
    </a>
    <div class="border-t border-gray-200"></div>
    <a href="../handlers/logout_handler" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
        Sign out
    </a>
</div>

<!-- Breadcrumbs -->
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        <li>
            <a href="dashboard" class="text-gray-500 hover:text-gray-700">Dashboard</a>
        </li>
        <!-- ... -->
    </ol>
</nav> 