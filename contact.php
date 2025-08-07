<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Support</title>
  <script defer>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('contactForm');
      const status = document.getElementById('status');

      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = new URLSearchParams(formData);

        const response = await fetch('contact_message.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: data,
        });

        const result = await response.json();

        if (result.success) {
          status.innerHTML = `<p style="color:green;">Message sent successfully.</p>`;
          form.reset();
        } else {
          status.innerHTML = `<p style="color:red;">${result.message}</p>`;
        }
      });
    });
  </script>
</head>
<body>
  <h1>Contact Customer Support</h1>
  <form id="contactForm">
    <label>
      Name:<br>
      <input type="text" name="name" required>
    </label><br><br>

    <label>
      Email:<br>
      <input type="email" name="email" required>
    </label><br><br>

    <label>
      Subject:<br>
      <input type="text" name="subject" required>
    </label><br><br>

    <label>
      Message:<br>
      <textarea name="message" required></textarea>
    </label><br><br>

    <button type="submit">Send Message</button>
  </form>

  <div id="status"></div>
</body>
</html>
