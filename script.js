document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault(); // prevent page refresh

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const message = document.getElementById("message");

  // create AJAX request
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "login.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function () {
    if (xhr.status === 200) {
      message.textContent = xhr.responseText;
      if (xhr.responseText.includes("Welcome")) {
        message.style.color = "green";
      } else {
        message.style.color = "red";
      }
    } else {
      message.textContent = "Server error!";
      message.style.color = "red";
    }
  };

  xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
});
