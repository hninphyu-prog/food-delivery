<?php
session_start();
require_once "../../config/db.php";
include "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Reviews</title>

<style>
/* TABLE */
.review-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.review-table th, .review-table td {
  padding: 14px 18px;
  text-align: left;
  font-size: 14px;
}
.review-table th {
  background: linear-gradient(90deg, #ff8800, #ff6600);
  color: #fff;
  font-weight: 600;
}
.review-table tr:nth-child(even) { background: #fafafa; }
.review-table tr:hover { background: #fff3e6; transition: 0.3s; }

/* Rating badges */
.rating {
  font-weight: bold;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 13px;
  display: inline-block;
  min-width: 40px;
  text-align: center;
}
.rating-good { background: #4CAF50; color: #fff; }
.rating-average { background: #FFEB3B; color: #333; }
.rating-bad { background: #F44336; color: #fff; }

/* Row highlight */
.review-good { background: #e8f5e9 !important; }
.review-average { background: #fffde7 !important; }
.review-bad { background: #ffebee !important; }

.review-good:hover { background: #c8e6c9 !important; }
.review-average:hover { background: #fff9c4 !important; }
.review-bad:hover { background: #ffcdd2 !important; }

/* Status button */
.status-toggle {
    padding: 6px 14px;
    border: none;
    cursor: pointer;
    color: #fff;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}
.status-visible { background-color: #4caf50; }
.status-hidden  { background-color: #f44336; }
</style>

</head>
<body>

<div class="main-content">
<h2 style="text-align:center; color:#ff6600;">📋 Customer Reviews</h2>

<!-- 🔍 SEARCH BAR -->
<div style="text-align:center; margin:20px 0;">
    <input type="text" id="search" 
           placeholder="Search by user, restaurant, or rating..."
           style="width:300px; padding:10px; border-radius:6px; border:1px solid #ccc;">
</div>

<!-- 🔄 AJAX Result Container -->
<div id="review-results">
<?php
$sql = "
  SELECT r.review_id, u.name AS user_name, res.name AS restaurant_name,
         r.rating, r.comment, r.status
  FROM reviews r
  LEFT JOIN users u ON r.user_id = u.user_id
  LEFT JOIN restaurants res ON r.restaurant_id = res.restaurant_id
  ORDER BY r.review_id DESC
";

$stmt = $pdo->query($sql);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($reviews):
?>
<table class="review-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>User</th>
      <th>Restaurant</th>
      <th>Rating</th>
      <th>Comment</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>

<?php foreach ($reviews as $review): 
    if ($review['rating'] >= 4) { $ratingClass = "rating-good"; $rowClass = "review-good"; }
    elseif ($review['rating'] == 3) { $ratingClass = "rating-average"; $rowClass = "review-average"; }
    else { $ratingClass = "rating-bad"; $rowClass = "review-bad"; }
?>
<tr class="<?= $rowClass ?>">
    <td><?= $review['review_id'] ?></td>
    <td><?= htmlspecialchars($review['user_name']) ?></td>
    <td><?= htmlspecialchars($review['restaurant_name']) ?></td>

    <td><span class="rating <?= $ratingClass ?>"><?= $review['rating'] ?>/5</span></td>

    <td title="<?= htmlspecialchars($review['comment']) ?>">
      <?= htmlspecialchars($review['comment']) ?>
    </td>

    <td>
      <button class="status-toggle 
        <?= $review['status'] === 'visible' ? 'status-visible' : 'status-hidden' ?>"
        data-id="<?= $review['review_id'] ?>"
        data-status="<?= $review['status'] ?>">
        <?= ucfirst($review['status']) ?>
      </button>
    </td>
</tr>
<?php endforeach; ?>

  </tbody>
</table>

<?php else: ?>
<p style="text-align:center;">No reviews found.</p>
<?php endif; ?>
</div>

<script>
// 🔄 STATUS TOGGLE
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("status-toggle")) {
        const btn = e.target;
        const id = btn.dataset.id;
        const currentStatus = btn.dataset.status;

        fetch("update_review_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}&status=${currentStatus}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const newStatus = data.new_status;
                btn.dataset.status = newStatus;
                btn.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                btn.classList.toggle("status-visible", newStatus === "visible");
                btn.classList.toggle("status-hidden", newStatus === "hidden");
            }
        });
    }
});

// 🔍 DYNAMIC SEARCH
document.getElementById("search").addEventListener("keyup", function () {
    const query = this.value;

    fetch("reviews_ajax.php?search=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
            document.getElementById("review-results").innerHTML = data;
        });
});
</script>

</div>
</body>
</html>
