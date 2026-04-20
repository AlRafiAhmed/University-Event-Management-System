<?php
// Static homepage (PHP version)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>IUBAT Event Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <nav class="bg-blue-900 text-white">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">IUBAT Event System</h1>
      <a href="login.php" class="bg-white text-blue-900 px-4 py-2 rounded hover:bg-gray-200 transition">Login</a>
    </div>
  </nav>

  <header class="max-w-7xl mx-auto px-4 py-10 text-center">
    <h2 class="text-3xl font-bold text-gray-800">University Event Management System</h2>
    <p class="text-gray-600 mt-2">Manage events, registrations, and approvals in one place.</p>
  </header>

  <section class="max-w-7xl mx-auto px-4 pb-12 grid md:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-5">
      <h3 class="text-lg font-semibold text-blue-900">Career Fair 2026</h3>
      <p class="text-gray-600 mt-2">Connect with top companies and industry leaders.</p>
      <p class="text-sm text-gray-500 mt-3">Date: 2026-05-10</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
      <h3 class="text-lg font-semibold text-blue-900">Tech Innovation Fest</h3>
      <p class="text-gray-600 mt-2">Showcase student projects and innovation ideas.</p>
      <p class="text-sm text-gray-500 mt-3">Date: 2026-06-02</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5">
      <h3 class="text-lg font-semibold text-blue-900">Cultural Evening</h3>
      <p class="text-gray-600 mt-2">Enjoy performances, music, and cultural activities.</p>
      <p class="text-sm text-gray-500 mt-3">Date: 2026-06-20</p>
    </div>
  </section>
</body>
</html>
