<?php
require_once "session_bootstrap.php";
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.html");
    exit;
}

$studentId = (int)($_SESSION["user_id"] ?? 0);
$stmt = $conn->prepare("SELECT is_active FROM Students WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $stmt->close();
        session_unset();
        session_destroy();
        header("Location: login.html?error=" . urlencode("Your account is deactive."));
        exit;
    }
    $stmt->bind_result($isActive);
    $stmt->fetch();
    $stmt->close();
    if ((int)$isActive !== 1) {
        session_unset();
        session_destroy();
        header("Location: login.html?error=" . urlencode("Your account is deactive."));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
  <div class="flex min-h-screen">
    <aside class="w-64 bg-blue-900 text-white p-5">
      <h2 class="text-xl font-bold mb-6">Student Panel</h2>
      <nav class="space-y-2">
        <a href="#dashboardTop" class="block px-3 py-2 rounded bg-blue-700">Dashboard</a>
        <a href="#eventsSection" class="block px-3 py-2 rounded hover:bg-blue-800">Events</a>
      </nav>
      <a href="logout.php" class="inline-block mt-8 bg-red-600 px-4 py-2 rounded hover:bg-red-700">Logout</a>
    </aside>

    <main class="flex-1 p-8">
    <div id="dashboardTop" class="bg-white shadow rounded p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-blue-900">Student Dashboard</h1>
          <p class="text-gray-600 mt-2">Welcome, <?php echo htmlspecialchars($_SESSION["name"] ?? "Student"); ?>.</p>
        </div>
      </div>
    </div>

    <div id="eventsSection" class="bg-white shadow rounded p-6 mt-6">
      <h2 class="text-2xl font-bold text-blue-900">Events</h2>
      <p class="text-gray-600 mt-1">Available events.</p>
      <div id="eventsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-5"></div>
    </div>
    <div id="eventDetailsSection" class="bg-white shadow rounded p-6 mt-6 hidden">
      <h2 class="text-2xl font-bold text-blue-900">Event Details</h2>
      <div id="eventBasicDetails" class="mt-3 text-sm text-slate-700 space-y-1"></div>
      <h3 class="text-xl font-bold text-slate-800 mt-6">Programs</h3>
      <div id="eventProgramsList" class="space-y-2 mt-3"></div>
    </div>
    </main>
  </div>
  <script>
    async function loadEvents() {
      const response = await fetch("events_list.php", { cache: "no-store" });
      if (!response.ok) return;
      const data = await response.json();
      if (!data.ok) return;

      const eventsGrid = document.getElementById("eventsGrid");
      if (!Array.isArray(data.events) || data.events.length === 0) {
        eventsGrid.innerHTML = '<p class="text-gray-500">No events found.</p>';
        return;
      }

      eventsGrid.innerHTML = data.events.map((event) => `
        <div class="border border-slate-200 rounded-lg bg-white">
          <div class="p-4">
            <h3 class="font-bold text-lg text-slate-800">${event.title}</h3>
            <p class="text-sm text-slate-600 mt-1">${event.event_type} - ${event.fee_type}</p>
            <p class="text-sm text-slate-600">Date: ${event.date}</p>
            <p class="text-sm text-slate-600">Location: ${event.location}</p>
            <button type="button" class="mt-3 bg-blue-700 text-white px-3 py-1.5 rounded hover:bg-blue-800 transition eventDetailsBtn"
              data-event='${JSON.stringify(event).replace(/'/g, "&apos;")}'>View Details</button>
          </div>
        </div>
      `).join("");

      document.querySelectorAll(".eventDetailsBtn").forEach((btn) => {
        btn.addEventListener("click", async function () {
          const event = JSON.parse(this.getAttribute("data-event").replace(/&apos;/g, "'"));
          await showEventDetails(event);
        });
      });
    }

    async function showEventDetails(event) {
      document.getElementById("eventDetailsSection").classList.remove("hidden");
      document.getElementById("eventBasicDetails").innerHTML = `
        <p><strong>Title:</strong> ${event.title}</p>
        <p><strong>Type:</strong> ${event.event_type} (${event.fee_type})</p>
        <p><strong>Date:</strong> ${event.date}</p>
        <p><strong>Last Registration:</strong> ${event.last_registration_date}</p>
        <p><strong>Location:</strong> ${event.location}</p>
        <p><strong>Capacity:</strong> ${event.capacity}</p>
        <p><strong>Description:</strong> ${event.description || "-"}</p>
      `;

      const response = await fetch("event_programs.php?event_id=" + encodeURIComponent(event.id), { cache: "no-store" });
      if (!response.ok) return;
      const data = await response.json();
      if (!data.ok) return;

      const programsList = document.getElementById("eventProgramsList");
      if (!Array.isArray(data.programs) || data.programs.length === 0) {
        programsList.innerHTML = '<p class="text-gray-500">No programs added yet.</p>';
        return;
      }

      programsList.innerHTML = data.programs.map((program) => `
        <div class="border border-slate-200 rounded p-3">
          <p class="font-semibold text-slate-800">#${program.serial_no} ${program.program_name}</p>
          <p class="text-sm text-slate-600">Participant: ${program.participant_name || "-"}</p>
          <p class="text-sm text-slate-600">Time: ${program.start_time} - ${program.end_time}</p>
        </div>
      `).join("");

      window.location.hash = "eventDetailsSection";
    }

    loadEvents();

    setInterval(async function () {
      try {
        const response = await fetch("session_check.php", { cache: "no-store" });
        const data = await response.json();
        if (!data.ok) {
          window.location.href = "logout.php?error=" + encodeURIComponent(data.message || "Your account is deactive.");
        }
      } catch (e) {
        // keep silent on temporary network issues
      }
    }, 5000);
  </script>
</body>
</html>
