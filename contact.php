<?php
include ('includes/header.php');

$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A simple placeholder for backend logic
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    $message_sent = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        :root {
            --theme-color: rgb(255, 102, 0);
            --dark-text: #333;
            --light-gray: #f4f4f4;
            --border-color: #ddd;
        }

        .containerr {
            max-width: 1100px;
            margin: 20px 100px;
           
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        h1 {
            color: var(--theme-color);
            font-size: 2.5em;
        }
        p {
            line-height: 1.6;
        }
        .contact-form .form-group {
            margin-bottom: 20px;
        }
        .contact-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .contact-form input, .contact-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            transition: border-color 0.3s;
        }
        .contact-form input:focus, .contact-form textarea:focus {
            outline: none;
            border-color: var(--theme-color);
        }
        .btn-submit {
            background-color: var(--theme-color);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: rgb(230, 90, 0);
        }
        .contact-info {
            padding: 20px;
            background-color: var(--light-gray);
            border-radius: 5px;
        }
        .contact-info h3 {
             border-bottom: 2px solid var(--theme-color);
             padding-bottom: 10px;
        }
        .contact-info p {
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .map-container {
            margin-top: 20px;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .containerr {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="containerr">
        <div class="contact-form">
            <h1>Get in Touch</h1>
            <p>We'd love to hear from you! Fill out the form below and we'll get back to you as soon as possible.</p>
            
            <?php if ($message_sent): ?>
                <h3>Thanks for your message! We'll be in touch.</h3>
            <?php else: ?>
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Send Message</button>
                </form>
            <?php endif; ?>

        </div>
        <div class="contact-info">
            <h3>Contact Information</h3>
            <p><strong>📍 Address:</strong> FLP Group Building A,Myanma Gon Yi Street,Thaketa Township,Yangon,Myanmar</p>
            <p><strong>📞 Phone:</strong> +959 262 007 800</p>
            <p><strong>📧 Email:</strong> www.foodandme@gmail.com</p>
            <div class="map-container">
               <iframe
  width="600"
  height="450"
  style="border:0;"
  loading="lazy"
  allowfullscreen
  referrerpolicy="no-referrer-when-downgrade"
  src="https://maps.google.com/maps?q=16.80583808440756, 96.19933439302598&hl=en&z=14&amp;output=embed">
</iframe>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
</body>
</html>