<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$db = getDB();

// Get popular dishes
$popular = $db->query("SELECT * FROM menu_items WHERE is_popular=1 AND is_available=1 LIMIT 6");

// Get stats
$totalOrders = $db->query("SELECT COUNT(*) as c FROM orders WHERE status='delivered'")->fetch_assoc()['c'];
$totalDishes = $db->query("SELECT COUNT(*) as c FROM menu_items WHERE is_available=1")->fetch_assoc()['c'];
$activeDeals = $db->query("SELECT COUNT(*) as c FROM waste_deals WHERE is_active=1 AND expires_at > NOW()")->fetch_assoc()['c'];

$emojiMap = ['Starters'=>'🥗','Main Course'=>'🍛','Breads'=>'🫓','Desserts'=>'🍮','Beverages'=>'🥤','Rice & Biryani'=>'🍚'];
function getEmoji($name) {
  global $emojiMap;
  foreach($emojiMap as $k=>$v) if(stripos($name,$k)!==false) return $v;
  return '🍽️';
}
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">✨ Smart Dining Experience</div>
      <h1>Great Food,<br><em>Zero Waste</em>,<br>Zero Wait</h1>
      <p>Reserve your table, pre-order your favorites, and enjoy exclusive discounted deals on surplus food — all in one place.</p>
      <div class="hero-actions">
        <a href="menu.php" class="btn btn-primary btn-lg">🍛 Browse Menu</a>
        <a href="reservation.php" class="btn btn-outline btn-lg" style="color:white;border-color:rgba(255,255,255,0.4)">🪑 Reserve Table</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="<?php echo $totalOrders; ?>"><?php echo $totalOrders; ?></div>
          <div class="hero-stat-label">Orders Served</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="<?php echo $totalDishes; ?>"><?php echo $totalDishes; ?></div>
          <div class="hero-stat-label">Menu Items</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num" data-count="<?php echo $activeDeals; ?>"><?php echo $activeDeals; ?></div>
          <div class="hero-stat-label">Live Deals</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features -->
<section class="section" style="background:white">
  <div class="container">
    <div class="section-title">
      <h2>Why Dine with EatSmart?</h2>
      <p>We make your dining experience smarter and more sustainable</p>
    </div>
    <div class="grid grid-4">
      <div class="card card-body text-center" style="padding:30px 20px">
        <div style="font-size:2.5rem;margin-bottom:12px">🪑</div>
        <h3 style="margin-bottom:8px">Table Reservation</h3>
        <p style="font-size:0.88rem">Book your table with a specific time slot. No more waiting in queues.</p>
      </div>
      <div class="card card-body text-center" style="padding:30px 20px">
        <div style="font-size:2.5rem;margin-bottom:12px">⏱️</div>
        <h3 style="margin-bottom:8px">Pre-Order Food</h3>
        <p style="font-size:0.88rem">Order your meal before arrival. It'll be ready when you walk in.</p>
      </div>
      <div class="card card-body text-center" style="padding:30px 20px">
        <div style="font-size:2.5rem;margin-bottom:12px">🌱</div>
        <h3 style="margin-bottom:8px">Waste Reduction</h3>
        <p style="font-size:0.88rem">Grab deeply discounted surplus meals. Good for you and the planet.</p>
      </div>
      <div class="card card-body text-center" style="padding:30px 20px">
        <div style="font-size:2.5rem;margin-bottom:12px">📦</div>
        <h3 style="margin-bottom:8px">Live Order Status</h3>
        <p style="font-size:0.88rem">Track your order in real-time from accepted to ready to serve.</p>
      </div>
    </div>
  </div>
</section>

<!-- Popular Dishes -->
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>⭐ Most Popular Dishes</h2>
      <p>Loved by hundreds of our guests</p>
    </div>
    <div class="grid grid-3">
      <?php while($dish = $popular->fetch_assoc()): 
        $emoji = getEmoji($dish['name']);
      ?>
      <div class="menu-card" onclick="Cart.add(<?php echo $dish['id']; ?>, '<?php echo addslashes($dish['name']); ?>', <?php echo $dish['price']; ?>, '<?php echo $emoji; ?>')">
        <div class="menu-card-img">
          <?php if(!empty($dish['image_url'])): ?>
            <img src="<?php echo SITE_URL . '/' . $dish['image_url']; ?>"
              alt="<?php echo sanitize($dish['name']); ?>"
              style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <span style="font-size:3rem">🍽️</span>
          <?php endif; ?>
          <span class="menu-card-badge">⭐ Popular</span>
        </div>
        <div class="menu-card-body">
          <div class="menu-card-name"><?php echo sanitize($dish['name']); ?></div>
          <div class="menu-card-desc"><?php echo sanitize($dish['description']); ?></div>
          <div class="menu-card-footer">
            <div class="menu-card-price">₹<?php echo number_format($dish['price'],0); ?> <span>/ serving</span></div>
            <button class="btn btn-primary btn-sm" onclick="event.stopPropagation();Cart.add(<?php echo $dish['id']; ?>, '<?php echo addslashes($dish['name']); ?>', <?php echo $dish['price']; ?>, '<?php echo $emoji; ?>')">+ Add</button>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-3">
      <a href="menu.php" class="btn btn-outline btn-lg">View Full Menu →</a>
    </div>
  </div>
</section>

<!-- Waste Deals Promo -->
<?php
$deals = $db->query("SELECT wd.*, mi.name, mi.description FROM waste_deals wd JOIN menu_items mi ON wd.menu_item_id=mi.id WHERE wd.is_active=1 AND wd.expires_at > NOW() LIMIT 3");
if($deals->num_rows > 0):
?>
<section class="section" style="background:linear-gradient(135deg,#1A1108,#2D1F0E)">
  <div class="container">
    <div class="section-title">
      <h2 style="color:var(--accent)">🌱 Today's Waste Reduction Deals</h2>
      <p style="color:rgba(255,255,255,0.5)">Help reduce food waste — grab surplus food at massive discounts</p>
    </div>
    <div class="grid grid-3">
      <?php while($deal = $deals->fetch_assoc()):
        $pct = round((1 - $deal['discounted_price']/$deal['original_price'])*100);
      ?>
      <div class="deal-card" style="background:white">
        <div class="deal-ribbon"><?php echo $pct; ?>% OFF</div>
        <div class="card-body" style="padding:24px;padding-top:36px">
          <div style="font-size:2rem;margin-bottom:10px">🍽️</div>
          <h3 style="margin-bottom:6px"><?php echo sanitize($deal['name']); ?></h3>
          <p style="font-size:0.84rem;margin-bottom:12px"><?php echo sanitize($deal['description']); ?></p>
          <div class="deal-discount">₹<?php echo number_format($deal['discounted_price'],0); ?></div>
          <div class="deal-original">was ₹<?php echo number_format($deal['original_price'],0); ?></div>
          <div class="deal-timer" data-expires="<?php echo $deal['expires_at']; ?>">Loading...</div>
          <div style="margin-top:6px;font-size:0.8rem;color:var(--text3)"><?php echo $deal['quantity_available']; ?> left</div>
          <button class="btn btn-primary btn-block mt-2" onclick="Cart.add(<?php echo $deal['menu_item_id']; ?>, '<?php echo addslashes($deal['name']); ?> (Deal)', <?php echo $deal['discounted_price']; ?>, '🌱')">Grab Deal</button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-3">
      <a href="waste_deals.php" class="btn btn-outline btn-lg" style="color:var(--accent);border-color:var(--accent)">See All Deals →</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section" style="background:white">
  <div class="container text-center">
    <h2 style="margin-bottom:12px">Ready for a smarter dining experience?</h2>
    <p style="margin-bottom:28px;max-width:500px;margin-left:auto;margin-right:auto">Join EatSmart today — reserve tables, pre-order meals and enjoy great discounts on surplus food.</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <?php if(!isLoggedIn()): ?>
      <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
      <?php endif; ?>
      <a href="reservation.php" class="btn btn-secondary btn-lg">Reserve a Table</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
