<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login & Upload PDF/Image</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md space-y-6">
    <h2 class="text-xl font-semibold text-gray-700">JWT Login + Upload</h2>

    <!-- Login Form -->
    <form id="loginForm" class="space-y-3">
        <input type="email" name="email" placeholder="Email"
               class="block w-full border rounded-lg p-2 text-sm" required>
        <input type="password" name="password" placeholder="Password"
               class="block w-full border rounded-lg p-2 text-sm" required>
        <button type="submit"
                class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">
            Login
        </button>
    </form>

    <!-- Upload Form -->
    <form id="uploadForm" enctype="multipart/form-data" class="space-y-3 hidden">
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
               class="block w-full border rounded-lg p-2 text-sm" required>
        <button type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
            Upload File
        </button>
    </form>

    <div id="response" class="text-sm"></div>
</div>

<script>
    let jwtToken = null;

    // 🔹 Handle Login
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const email = formData.get("email");
        const password = formData.get("password");

        const res = await fetch("{{ url('/api/auth/login') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, password })
        });

        const data = await res.json();
        const responseDiv = document.getElementById('response');

        if (res.ok && data.token) {
            jwtToken = data.token; // ✅ Save token
            document.getElementById('uploadForm').classList.remove("hidden"); // ✅ Show upload form
            responseDiv.innerHTML =
                `<p class="text-green-600 font-medium">✅ Login Success</p>
                 <pre class="bg-gray-100 p-2 rounded">${JSON.stringify(data, null, 2)}</pre>`;
        } else {
            responseDiv.innerHTML =
                `<p class="text-red-600 font-medium">❌ Login Failed</p>
                 <pre class="bg-gray-100 p-2 rounded">${JSON.stringify(data, null, 2)}</pre>`;
        }
    });

    // 🔹 Handle File Upload
    document.getElementById('uploadForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!jwtToken) {
            alert("⚠️ Please login first!");
            return;
        }

        const formData = new FormData(this);
        const responseDiv = document.getElementById('response');
        responseDiv.innerHTML = "⏳ Uploading...";

        const res = await fetch("{{ url('/api/import') }}", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + jwtToken   // ✅ Use saved JWT
            },
            body: formData
        });

        const text = await res.text();
        try {
            const data = JSON.parse(text);
            responseDiv.innerHTML =
                `<p class="text-green-600 font-medium">✅ Upload Success</p>
                 <pre class="bg-gray-100 p-2 rounded">${JSON.stringify(data, null, 2)}</pre>`;
        } catch (err) {
            responseDiv.innerHTML =
                `<p class="text-red-600 font-medium">❌ Upload Error (not JSON)</p>
                 <pre class="bg-gray-100 p-2 rounded">${text}</pre>`;
        }
    });
</script>

</body>
</html>
