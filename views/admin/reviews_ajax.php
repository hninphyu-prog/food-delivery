<?php
session_start();
require_once "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "
    SELECT r.review_id, u.name AS user_name, res.name AS restaurant_name,
           r.rating, r.comment, r.status
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.user_id
    LEFT JOIN restaurants res ON r.restaurant_id = res.restaurant_id
    WHERE u.name LIKE :search
       OR res.name LIKE :search
       OR r.rating LIKE :search
    ORDER BY r.review_id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
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
<p style="text-align:center; padding:20px;">No reviews found.</p>
<?php endif; ?>
