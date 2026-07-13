<?php include ('includes/header.php')?>
<main id="fm-about" aria-labelledby="fm-about-title">
  <style>
    /* Scoped to #fm-about to avoid collisions with your header/site CSS */
    #fm-about{background: linear-gradient(rgba(233, 231, 231, 0.9), rgba(255, 102, 0, 0.9)); color: #ece0e0ff; padding:48px 16px; box-sizing:border-box}
    #fm-about .fm-container{max-width:1100px;margin:0 auto;display:grid;gap:24px}
    /* Hero */
    #fm-about .fm-hero{
      display:flex;flex-direction:column;gap:14px;padding:28px;border-radius:12px;
      background:#ffffff; /* white card on orange background */
      color: rgb(255,102,0); /* headings/text in brand orange */
      box-shadow: 0 10px 30px rgba(255,102,0,0.06);
    }
    #fm-about .fm-brand{display:flex;align-items:center;gap:12px}
    #fm-about .fm-logo{
      width:56px;height:56px;border-radius:10px;background:rgb(255,102,0);display:grid;place-items:center;
      color:white;font-weight:700;font-size:20px;
    }
    #fm-about h1{margin:0;font-size:26px;line-height:1.05}
    #fm-about p{margin:0;color:rgb(255,102,0);opacity:0.95;line-height:1.5}
    /* CTA buttons (scoped) */
    #fm-about .fm-cta{display:flex;gap:12px;margin-top:12px}
    #fm-about .fm-btn{
      padding:10px 16px;border-radius:10px;border:2px solid transparent;
      background:rgb(255,102,0);color:white;text-decoration:none;display:inline-flex;align-items:center;gap:8px;
      font-weight:600;cursor:pointer;
    }
    #fm-about .fm-btn--ghost{
      background:transparent;color:white;border:2px solid white;
    }

    /* Grid cards */
    #fm-about .fm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    #fm-about .fm-card{background:white;border-radius:10px;padding:16px;color:rgb(255,102,0);box-shadow:0 8px 24px rgba(255,102,0,0.04)}
    #fm-about .fm-card h3{margin:0 0 8px;font-size:16px}
    #fm-about .fm-list{margin:0;padding-left:18px;color:rgb(255,102,0)}

    /* Team */
    #fm-about .fm-team{display:flex;gap:12px;flex-wrap:wrap}
    #fm-about .fm-person{display:flex;gap:12px;align-items:center;padding:10px;border-radius:10px;background:rgba(255,255,255,0.12);}
    #fm-about .fm-avatar{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;background:white;color:rgb(255,102,0);font-weight:700}

    /* Footer small */
    #fm-about .fm-footer{color:white;opacity:0.95;font-size:30px;margin-top:6px}

    /* Responsive */
    @media (max-width:900px){
      #fm-about .fm-grid{grid-template-columns:1fr}
      #fm-about .fm-hero{padding:18px}
    }
  </style>

  <div class="fm-container">
    <section class="fm-hero" aria-labelledby="fm-about-title">
      <div class="fm-brand">
        <div class="fm-logo" aria-hidden="true">F&amp;M</div>
        <div>
          <strong style="font-size:18px;display:block">Food&Me</strong>
          <small style="display:block;color:rgb(255,102,0);opacity:0.9">Local favorites, delivered fast</small>
        </div>
      </div>

      <h1 id="fm-about-title">We bring your favorite local restaurants to your door.</h1>
      <p>Food&Me is a student-built PHP group project focused on a simple, fast ordering experience — clear menus, order tracking, and friendly design.</p>

      <div class="fm-cta" role="group" aria-label="Call to action">
        <a class="fm-btn" href="/foodandme/menu.php" role="button"><i class="fas fa-utensils" aria-hidden="true"></i> Explore Menu</a>
        <a class="fm-btn fm-btn--ghost" href="/foodandme/contact.php" role="button">Contact Us</a>
      </div>
    </section>

    <section class="fm-grid" aria-label="mission and features">
      <div class="fm-card">
        <h3>Our mission</h3>
        <p>Help people discover the best local food while providing restaurants a lightweight, reliable ordering channel.</p>
      </div>

      <div class="fm-card">
        <h3>How it works</h3>
        <ol class="fm-list">
          <li>Browse curated local menus.</li>
          <li>Place an order for pickup or delivery.</li>
          <li>Track your order to the door.</li>
        </ol>
      </div>

<div class="fm-card">
    <h3>Partner Advantages</h3>
    <ul class="fm-list">
        <li>Grow Your Business </li>
        <li> Easy Setup - Get your restaurant online </li>
        <li> Reliable Payments - Weekly settlements, no delays</li>
    </ul>
</div>
    </section>

    

    <footer class="fm-footer">
      <small>© <span id="fm-year"></span> Food&Me — Built with PHP &amp; ❤️</small>
    </footer>
  </div>

  <script>
    // set year
    document.getElementById('fm-year').textContent = (new Date()).getFullYear();
  </script>
</main>
<?php include 'includes/footer.php'; ?>