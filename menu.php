<?php
$pageTitle = 'Menu';
require_once 'includes/header.php';
$db = getDB();

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order");
$catArr = [];
while($c = $categories->fetch_assoc()) $catArr[] = $c;

$items = $db->query("SELECT mi.*, c.name as cat_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id=c.id WHERE mi.is_available=1 ORDER BY c.sort_order, mi.name");
$menuItems = [];
while($item = $items->fetch_assoc()) $menuItems[] = $item;

$emojiMap = [
  'Paneer Tikka'=>'🧀','Chicken 65'=>'🍗','Spring Rolls'=>'🥢','Seekh Kebab'=>'🍢',
  'Butter Chicken'=>'🍛','Dal Makhani'=>'🫘','Paneer Butter Masala'=>'🧀','Mutton Rogan Josh'=>'🍖',
  'Palak Paneer'=>'🥬','Butter Naan'=>'🫓','Garlic Naan'=>'🫓','Tandoori Roti'=>'🌾',
  'Gulab Jamun'=>'🍯','Rasgulla'=>'⚪','Kulfi'=>'🍦','Mango Lassi'=>'🥭',
  'Masala Chai'=>'☕','Fresh Lime Soda'=>'🍋','Chicken Biryani'=>'🍚','Veg Biryani'=>'🍚','Egg Fried Rice'=>'🍳'
];
function getItemEmoji($name) {
  global $emojiMap;
  foreach($emojiMap as $k=>$v) if(stripos($name,$k)!==false) return $v;
  return '🍽️';
}

// AI: popular dishes by order count
$popQuery = $db->query("SELECT menu_item_id, COUNT(*) as cnt FROM order_items GROUP BY menu_item_id ORDER BY cnt DESC LIMIT 5");
$popularIds = [];
while($r=$popQuery->fetch_assoc()) $popularIds[] = $r['menu_item_id'];
?>

<section class="section" style="padding-top:20px">
  <div class="container">
    <div class="section-title">
      <h2>Our Menu</h2>
      <p>Fresh, authentic flavors prepared with love</p>
    </div>

    <!-- Category Filter Tabs -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;justify-content:center">
      <button class="btn btn-primary btn-sm" onclick="filterCat('all',this)" id="catAll">All Items</button>
      <?php foreach($catArr as $cat): ?>
      <button class="btn btn-secondary btn-sm" onclick="filterCat('<?php echo $cat['id']; ?>',this)" id="cat<?php echo $cat['id']; ?>">
        <?php echo $cat['icon'].' '.$cat['name']; ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Search bar -->
    <div style="max-width:400px;margin:0 auto 28px">
      <input type="text" id="menuSearch" class="form-control" placeholder="🔍  Search dishes..." oninput="searchMenu(this.value)">
    </div>

    <!-- Menu Grid -->
    <div class="grid grid-3" id="menuGrid">
      <?php foreach($menuItems as $item):
        $emoji = getItemEmoji($item['name']);
        $isPopular = in_array($item['id'], $popularIds);
      ?>
      <div class="menu-card" data-cat="<?php echo $item['category_id']; ?>" data-name="<?php echo strtolower($item['name']); ?>" data-desc="<?php echo strtolower($item['description']); ?>">
        <div class="menu-card-img">
          <?php if(!empty($item['image_url'])): ?>
            <img src="<?php echo SITE_URL . '/' . $item['image_url']; ?>" 
              alt="<?php echo sanitize($item['name']); ?>"
              style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <span style="font-size:3rem">🍽️</span>
          <?php endif; ?>
          <?php if($isPopular): ?><span class="menu-card-badge">⭐ Popular</span><?php endif; ?>
          <?php if($item['prep_time'] <= 10): ?><span class="menu-card-badge" style="background:var(--success);top:auto;bottom:10px">⚡ Quick</span><?php endif; ?>
        </div>

        
        <div class="menu-card-body">
          <div class="menu-card-name"><?php echo sanitize($item['name']); ?></div>
          <div class="menu-card-desc"><?php echo sanitize($item['description']); ?></div>
          <div style="font-size:0.78rem;color:var(--text3);margin-bottom:10px">
            ⏱ <?php echo $item['prep_time']; ?> mins
            <span style="margin-left:8px">📂 <?php echo sanitize($item['cat_name']); ?></span>
          </div>
          <div class="menu-card-footer">
            <div class="menu-card-price">₹<?php echo number_format($item['price'],0); ?></div>
            <button class="btn btn-primary btn-sm" onclick="Cart.add(<?php echo $item['id']; ?>,'<?php echo addslashes($item['name']); ?>',<?php echo $item['price']; ?>,'<?php echo $emoji; ?>')">
              + Add
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- AI Recommendations -->
    <?php if(!empty($popularIds) && isLoggedIn()): ?>
    <div style="margin-top:40px;padding:24px;background:linear-gradient(135deg,#FDF8F3,#F5EDE0);border-radius:var(--radius);border:1px solid var(--border)">
      <h3 style="margin-bottom:4px">🤖 AI Picks — Most Ordered Today</h3>
      <p style="font-size:0.85rem;margin-bottom:16px">Based on order history from all customers</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php
        $placeholders = implode(',', array_fill(0, count($popularIds), '?'));
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders) AND is_available=1 LIMIT 5");
        $types = str_repeat('i', count($popularIds));
        $stmt->bind_param($types, ...$popularIds);
        $stmt->execute();
        $recItems = $stmt->get_result();
        while($rec = $recItems->fetch_assoc()):
          $em = getItemEmoji($rec['name']);
        ?>
        <div style="background:white;border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;display:flex;align-items:center;gap:10px;cursor:pointer" onclick="Cart.add(<?php echo $rec['id']; ?>,'<?php echo addslashes($rec['name']); ?>',<?php echo $rec['price']; ?>,'🍽️')">
          <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--surface2);display:flex;align-items:center;justify-content:center;">
            <?php if(!empty($rec['image_url'])): ?>
              <img src="<?php echo SITE_URL . '/' . $rec['image_url']; ?>"
                alt="<?php echo sanitize($rec['name']); ?>"
                style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <span style="font-size:1.4rem">🍽️</span>
            <?php endif; ?>
          </div>
          <div>

            <div style="font-weight:500;font-size:0.88rem"><?php echo sanitize($rec['name']); ?></div>
            <div style="color:var(--primary);font-weight:600;font-size:0.85rem">₹<?php echo number_format($rec['price'],0); ?></div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Empty state -->
    <div id="menuEmpty" class="empty-state" style="display:none">
      <div class="icon">🔍</div>
      <p>No dishes found. Try a different search.</p>
    </div>
  </div>
</section>

<script>
function filterCat(catId, btn) {
  document.querySelectorAll('#menuGrid .menu-card').forEach(card => {
    card.style.display = (catId === 'all' || card.dataset.cat === catId) ? '' : 'none';
  });
  document.querySelectorAll('.btn[id^="cat"]').forEach(b => { b.className = 'btn btn-secondary btn-sm'; });
  btn.className = 'btn btn-primary btn-sm';
  checkEmpty();
}
function searchMenu(q) {
  const lower = q.toLowerCase();
  document.querySelectorAll('#menuGrid .menu-card').forEach(card => {
    const match = card.dataset.name.includes(lower) || card.dataset.desc.includes(lower);
    card.style.display = match ? '' : 'none';
  });
  checkEmpty();
}
function checkEmpty() {
  const visible = [...document.querySelectorAll('#menuGrid .menu-card')].filter(c => c.style.display !== 'none');
  document.getElementById('menuEmpty').style.display = visible.length === 0 ? 'block' : 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
