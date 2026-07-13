<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .back-btn:hover{
        color: black;
    }
    .back-btn{
        color: white;
        text-decoration: none;
        font-weight: bold;
        font-size: 18px;
    }
</style>
<header class="sticky-header">
    <div class="container header-inner">
        <a href="dashboard.php?page=restaurants" class="back-btn" >
            ⬅ Back to Dashboard
        </a>
            <h2 style=" color: white;margin-right:40%;">All Carts</h2>
            
    </div>
</header>