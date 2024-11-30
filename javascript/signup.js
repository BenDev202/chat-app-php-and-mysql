// Add this to your JavaScript file or in a <script> tag
document.querySelector(".signup form").addEventListener("submit", function(e) {
  e.preventDefault();
  let formData = new FormData(this);
  
  fetch("signup.php", {
      method: "POST",
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert(data.message);
          window.location.href = "chat.html";
      } else {
          alert(data.message);
      }
  })
  .catch(error => console.error('Error:', error));
});