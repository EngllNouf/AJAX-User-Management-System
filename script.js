document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault(); // prevent page refresh

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const message = document.getElementById("message");

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "login.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        // نحول الرد من نص إلى JSON
        const data = JSON.parse(xhr.responseText);

        message.textContent = data.message;
        message.style.color = data.success ? "green" : "red";

        // ✅ إذا تم تسجيل الدخول بنجاح نحول المستخدم إلى صفحة Users.html
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        }

      } catch (err) {
        // لو الرد مو JSON أو فيه خطأ
        message.textContent = "Unexpected response from server!";
        message.style.color = "red";
        console.error("Parsing error:", err, xhr.responseText);
      }

    } else {
      message.textContent = "Server error!";
      message.style.color = "red";
    }
  };

  xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
});
