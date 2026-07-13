<?php
if (!isset($pageTitle)) $pageTitle = 'Home';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> — Food&amp;Me</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  
  <style>
    /* Sign In Button */
.signin-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background-color:  rgb(255,102,0);
    color: #ffffff !important;
    border: 2px solid  rgb(255,102,0);
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.signin-btn:hover {
    background-color: #333333;
    border-color: #333333;
}

.signin-btn i {
    font-size: 0.9rem;
}
  </style>

</head>
<body>
<header class="site-header">
  <div class="container header-inner ">
    <a href="/foodandme/index.php" class="brand">
      <div class="brand__logo">
        <i class="fas fa-utensils"></i>
      </div>
      <span class="brand__name">Food<span>&amp;</span>Me</span>
    </a>

    <nav class="main-nav" id="main-nav" aria-label="Main navigation">
      <ul class="main-nav__list">
        <li><a href="/foodandme/index.php">Home</a></li>
        <li><a href="/foodandme/about.php">About</a></li>
        <li><a href="/foodandme/partners.php">Partners</a></li>
        <li><a href="/foodandme/contact.php">Contact</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <a href="/foodandme/login.php" class="signin-btn">
        <i class="fas fa-user"></i> SIGN IN
      </a>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
      </button>
    </div>
  </div>
</header>

<script>
  // Toggle mobile navigation
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('main-nav');
  
  navToggle.addEventListener('click', () => {
    navToggle.classList.toggle('active');
    mainNav.classList.toggle('active');
    navToggle.setAttribute('aria-expanded', 
      navToggle.getAttribute('aria-expanded') === 'false' ? 'true' : 'false');
  });
  
  // Close mobile nav when clicking on a link
  document.querySelectorAll('.main-nav__list a').forEach(link => {
    link.addEventListener('click', () => {
      navToggle.classList.remove('active');
      mainNav.classList.remove('active');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });
  
</script>