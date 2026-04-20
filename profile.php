<?php
require_once "session_bootstrap.php";
if (!isset($_SESSION["role"], $_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
  <div class="max-w-4xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-blue-900">My Profile</h1>
        <a id="backLink" href="#" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">Back</a>
      </div>
      <p class="text-gray-600 mt-2">Profile information is view only.</p>

      <div id="profileViewSection" class="mt-6">
        <div class="flex items-center gap-4">
          <img id="profileViewImage" src="https://placehold.co/120x120?text=Profile" alt="Profile" class="w-24 h-24 rounded-full object-cover border border-slate-300" />
          <div>
            <h2 id="profileViewName" class="text-2xl font-bold text-slate-800"></h2>
            <p id="profileViewRole" class="text-sm text-slate-600 capitalize"></p>
          </div>
        </div>
        <div id="profileDetailsGrid" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4"></div>
        <button id="editProfilePicBtn" type="button" class="mt-6 bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-800">Edit Profile Picture</button>
      </div>

      <form id="profilePicForm" class="hidden mt-6 border border-slate-200 rounded-lg p-5 bg-slate-50" enctype="multipart/form-data">
        <input type="hidden" name="current_profile_image" id="current_profile_image" />
        <label class="block text-sm font-semibold text-gray-700 mb-2">Choose New Profile Picture</label>
        <input type="file" name="profile_image" accept="image/*" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 bg-white" />
        <div class="mt-4 flex items-center gap-3">
          <button type="submit" class="bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-800">Save Picture</button>
          <button id="cancelProfilePicBtn" type="button" class="bg-slate-200 text-slate-800 px-5 py-2.5 rounded-lg font-semibold hover:bg-slate-300">Cancel</button>
          <p id="profilePicMessage" class="text-sm font-medium"></p>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentRole = "";

    function setBackLink(role) {
      const map = {
        admin: "admin_dashboard.html",
        supervisor: "supervisor_dashboard.php",
        student: "student_dashboard.php"
      };
      document.getElementById("backLink").href = map[role] || "login.html";
    }

    function renderProfileView(role, profile) {
      document.getElementById("profileViewImage").src = profile.profile_image || "https://placehold.co/120x120?text=Profile";
      document.getElementById("profileViewName").textContent = profile.name || "-";
      document.getElementById("profileViewRole").textContent = role || "";

      const details = [];
      if (role === "admin") {
        details.push({ label: "Name", value: profile.name || "-" });
        details.push({ label: "Email", value: profile.email || "-" });
      }
      if (role === "student") {
        details.push({ label: "Student ID", value: profile.student_id || "-" });
        details.push({ label: "Name", value: profile.name || "-" });
        details.push({ label: "Email", value: profile.email || "-" });
        details.push({ label: "Department", value: profile.department || "-" });
      }
      if (role === "supervisor") {
        details.push({ label: "Name", value: profile.name || "-" });
        details.push({ label: "Email", value: profile.email || "-" });
        details.push({ label: "Designation", value: profile.designation || "-" });
        details.push({ label: "Department", value: profile.department || "-" });
      }

      document.getElementById("profileDetailsGrid").innerHTML = details.map((item) => `
        <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
          <p class="text-xs uppercase tracking-wide text-slate-500">${item.label}</p>
          <p class="mt-1 text-base font-semibold text-slate-800">${item.value}</p>
        </div>
      `).join("");
    }

    async function loadProfile() {
      const response = await fetch("profile_manage.php", { cache: "no-store" });
      if (!response.ok) {
        window.location.href = "login.html";
        return;
      }
      const data = await response.json();
      if (!data.ok) {
        window.location.href = "login.html";
        return;
      }
      const profile = data.profile || {};
      currentRole = data.role || "";
      setBackLink(currentRole);
      document.getElementById("current_profile_image").value = profile.profile_image || "";
      renderProfileView(currentRole, profile);
      document.getElementById("profileViewSection").classList.remove("hidden");
      document.getElementById("profilePicForm").classList.add("hidden");
    }

    document.getElementById("editProfilePicBtn").addEventListener("click", function () {
      document.getElementById("profilePicForm").classList.remove("hidden");
    });

    document.getElementById("cancelProfilePicBtn").addEventListener("click", function () {
      document.getElementById("profilePicForm").classList.add("hidden");
      document.getElementById("profilePicMessage").textContent = "";
    });

    document.getElementById("profilePicForm").addEventListener("submit", async function (e) {
      e.preventDefault();
      const msg = document.getElementById("profilePicMessage");
      msg.textContent = "Saving...";
      msg.className = "text-sm font-medium text-blue-700";

      const response = await fetch("profile_manage.php", {
        method: "POST",
        body: new FormData(this)
      });
      const data = await response.json();
      if (response.ok && data.ok) {
        msg.textContent = data.message || "Profile picture updated.";
        msg.className = "text-sm font-medium text-green-700";
        this.reset();
        await loadProfile();
      } else {
        msg.textContent = data.message || "Could not update profile picture.";
        msg.className = "text-sm font-medium text-red-700";
      }
    });

    loadProfile();
  </script>
</body>
</html>
