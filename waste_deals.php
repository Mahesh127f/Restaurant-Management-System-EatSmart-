<?php
$pageTitle = 'Waste Reduction Deals';
require_once 'includes/header.php';
$db = getDB();

$deals = $db->query("SELECT wd.*, mi.name, mi.description, mi.image_url, c.name as cat_name FROM waste_deals wd JOIN menu_items mi ON wd.menu_item_id=mi.id LEFT JOIN categories c ON mi.category_id=c.id WHERE wd.is_active=1 AND wd.expires_at > NOW() ORDER BY wd.expires_at ASC");
$dealsArr = [];
while($d=$deals->fetch_assoc()) $dealsArr[] = $d;

$emojiMap = ['Mutton'=>'🍖','Chicken'=>'🍗','Paneer'=>'🧀','Dal'=>'🫘','Biryani'=>'🍚','Naan'=>'🫓','Roti'=>'🌾','Gulab'=>'🍯','Kulfi'=>'🍦','Lassi'=>'🥭'];
function getDealEmoji($name) {
  global $emojiMap;
  foreach($emojiMap as $k=>$v) if(stripos($name,$k)!==false) return $v;
  return '🍽️';
}
?>

<section class="section" style="background:linear-gradient(135deg,#1A1108,#2D1F0E);padding:40px 0">
  <div class="container text-center">
    <h1 style="color:white;margin-bottom:8px">🌱 Waste Reduction Deals</h1>
    <p style="color:rgba(255,255,255,0.6);max-width:600px;margin:0 auto;font-size:1rem">
      Our kitchen sometimes prepares more than needed. Instead of wasting it, we offer surplus food at massive discounts — helping the planet and your wallet!
    </p>
    <div style="display:flex;gap:24px;justify-content:center;margin-top:24px;flex-wrap:wrap">
      <div style="text-align:center">
        <div style="font-size:1.6rem;font-weight:700;color:var(--accent)" id="dealCount"><?php echo count($dealsArr); ?></div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.5)">Active Deals</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:1.6rem;font-weight:700;color:var(--accent)"><?php echo array_sum(array_column($dealsArr,'quantity_available')); ?></div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.5)">Portions Left</div>
      </div>
      <div style="text-align:center">
        <?php $maxSave = !empty($dealsArr) ? max(array_map(fn($d)=>$d['original_price']-$d['discounted_price'], $dealsArr)) : 0; ?>
        <div style="font-size:1.6rem;font-weight:700;color:var(--accent)">₹<?php echo number_format($maxSave,0); ?></div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.5)">Max Savings</div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if(empty($dealsArr)): ?>
    <div class="empty-state">
      <div class="icon">🌱</div>
      <h3 style="margin-bottom:8px">No active deals right now</h3>
      <p>Check back later — our kitchen updates deals throughout the day.</p>
      <a href="menu.php" class="btn btn-primary mt-3">Browse Full Menu</a>
    </div>
    <?php else: ?>
    <div class="grid grid-3">
      <?php foreach($dealsArr as $deal):
        $emoji = getDealEmoji($deal['name']);
        $pct = round((1 - $deal['discounted_price']/$deal['original_price'])*100);
        $savings = $deal['original_price'] - $deal['discounted_price'];
      ?>
      <div class="deal-card">
        <div class="deal-ribbon"><?php echo $pct; ?>% OFF</div>
        <div style="padding:28px 20px 20px;padding-top:44px">

          <div style="width:80px;height:80px;border-radius:10px;overflow:hidden;margin:0 auto 12px">
            <?php if(!empty($deal['image_url'] ?? '')): ?>
              <img src="<?php echo SITE_URL . '/' . $deal['image_url']; ?>"
                alt="<?php echo sanitize($deal['name']); ?>"
                style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <span style="font-size:3rem">🍽️</span>
            <?php endif; ?>
          </div>

          <h3 style="margin-bottom:6px"><?php echo sanitize($deal['name']); ?></h3>
          <p style="font-size:0.84rem;color:var(--text3);margin-bottom:14px"><?php echo sanitize($deal['description']); ?></p>

          <div style="background:var(--surface);border-radius:var(--radius-sm);padding:12px;margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <div>
                <div class="deal-discount">₹<?php echo number_format($deal['discounted_price'],0); ?></div>
                <div class="deal-original">MRP ₹<?php echo number_format($deal['original_price'],0); ?></div>
              </div>
              <div style="text-align:right">
                <div style="font-size:1rem;font-weight:700;color:var(--success)">Save ₹<?php echo number_format($savings,0); ?></div>
                <div style="font-size:0.78rem;color:var(--text3)"><?php echo $deal['quantity_available']; ?> portions left</div>
              </div>
            </div>
          </div>

          <div class="deal-timer mb-2" data-expires="<?php echo $deal['expires_at']; ?>">Loading timer...</div>

          <div style="margin-bottom:10px;font-size:0.8rem;color:var(--text3)">
            📂 <?php echo sanitize($deal['cat_name']); ?>
          </div>

          <?php if($deal['quantity_available'] > 0): ?>
          <button class="btn btn-primary btn-block" onclick="Cart.add(<?php echo $deal['menu_item_id']; ?>,'<?php echo addslashes($deal['name']); ?> 🌱 DEAL',<?php echo $deal['discounted_price']; ?>,'🌱')">
            🛒 Grab This Deal
          </button>
          <?php else: ?>
          <button class="btn btn-secondary btn-block" disabled>Sold Out</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- How it works -->
    <div class="card mt-4" style="background:linear-gradient(135deg,#E8F5EA,#F5EDE0)">
      <div class="card-body">
        <h3 style="margin-bottom:16px;text-align:center">🌍 How Waste Reduction Works</h3>
        <div class="grid grid-3" style="text-align:center">
          <div>
            <div style="font-size:2rem;margin-bottom:8px">👨‍🍳</div>
            <h4 style="margin-bottom:6px;font-size:0.95rem">Kitchen Marks Surplus</h4>
            <p style="font-size:0.82rem">Our admin marks extra prepared food that would otherwise go to waste</p>
          </div>
          <div>
            <div style="font-size:2rem;margin-bottom:8px">💸</div>
            <h4 style="margin-bottom:6px;font-size:0.95rem">Auto-Discounted for You</h4>
            <p style="font-size:0.82rem">The surplus gets listed here at 30–60% off for a limited time</p>
          </div>
          <div>
            <div style="font-size:2rem;margin-bottom:8px">🌱</div>
            <h4 style="margin-bottom:6px;font-size:0.95rem">Less Waste, More Savings</h4>
            <p style="font-size:0.82rem">You save money, we reduce food waste. Everyone wins!</p>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
