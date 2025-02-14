<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - Casita de Grands</title>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <script src="https://cdn.lordicon.com/bhenfmcm.js"></script>
  <style>
  [x-cloak] {
    display: none !important;
  }
  </style>
  <?php echo $extraStyles ?? ''; ?>
</head>

<body class="bg-gray-50">
  <div x-data="{ sidebarOpen: true }" x-cloak>
    <?php include('sidebar.php'); ?>

    <div class="flex-1">
      <?php include('header.php'); ?>
      <main class="p-8">
        <?php echo $content; ?>
      </main>
    </div>
  </div>
  <?php echo $extraScripts ?? ''; ?>
</body>

</html>