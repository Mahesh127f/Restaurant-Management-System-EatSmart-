<?php
$pageTitle = 'Menu Manager';
require_once '../php/config.php';
requireAdmin();
$db = getDB();
$msg = $err = '';

// Add / Edit item
if($_SERVER['REQUEST_METHOD']==='POST') {
  $id = (int)($_POST['id'] ?? 0);
  $name = sanitize($_POST['name'] ?? '');
  $desc = sanitize($_POST['description'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $catId = (int)($_POST['category_id'] ?? 0);
  $prep = (int)($_POST['prep_time'] ?? 15);
  $avail = isset($_POST['is_available']) ? 1 : 0;
  $pop = isset($_POST['is_popular']) ? 1 : 0;

  if(!$name || $price <= 0) { $err = 'Name and price are required.'; }
  else {
    if($id > 0) {
      $stmt = $db->prepare("UPDATE menu_items SET name=?,description=?,price=?,category_id=?,prep_time=?,is_available=?,is_popular=? WHERE id=?");
      $stmt->bind_param('ssdiiiiii', $name,$desc,$price,$catId,$prep,$avail,$pop,$id);
    } else {
      $stmt = $db->prepare("INSERT INTO menu_items (name,description,price,category_id,prep_time,is_available,is_popular) VALUES (?,?,?,?,?,?,?)");
      $stmt->bind_param('ssdiii', $name,$desc,$price,$catId,$prep,$avail,$pop);
    }
    $stmt->execute() ? ($msg = $id ? 'Item updated!' : 'Item added!') : ($err = 'Error saving.');
  }
}

// Delete
if(isset($_GET['delete'])) {
  $did = (int)$_GET['delete'];
  $db->query("UPDATE menu_items SET is_available=0 WHERE id=$did");
  $msg = 'Item hidden from menu.';
}

// Toggle availability
if(isset($_GET['toggle'])) {
  $tid = (int)$_GET['toggle'];
  $db->query("UPDATE menu_items SET is_available = 1 - is_available WHERE id=$tid");
  header('Location: menu.php'); exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order");
$catArr = []; while($c=$categories->fetch_assoc()) $catArr[] = $c;
$items = $db->query("SELECT mi.*, c.name as cat_name FROM menu_items mi LEFT JOIN categories c ON mi.category_id=c.id ORDER BY c.sort_order, mi.name");

// Edit mode
$editItem = null;
if(isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $es = $db->prepare("SELECT * FROM menu_items WHERE id=?");
  $es->bind_param('i',$eid); $es->execute();
  $editItem = $es->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Menu Manager — EatSmart Admin</title>
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1>🍛 Menu Manager</h1>
    <div class="topbar-actions">
      <button class="topbar-btn primary" onclick="document.getElementById('addModal').classList.add('open')">+ Add Item</button>
    </div>
  </div>
  <div class="admin-content">
    <?php if($msg): ?><div class="alert alert-success">✅ <?php echo $msg; ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger">⚠️ <?php echo $err; ?></div><?php endif; ?>

    <div class="data-card">
      <div class="data-card-header">
        <h3>All Menu Items (<?php echo $items->num_rows; ?>)</h3>
        <input type="text" id="menuSearch" class="form-control" style="width:200px" placeholder="Search..." oninput="filterTable(this.value)">
      </div>
      <div class="table-wrap">
        <table id="menuTable">
          <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Prep</th><th>Status</th><th>Popular</th><th>Actions</th></tr></thead>
          <tbody>
          <?php while($item=$items->fetch_assoc()): ?>
          <tr data-name="<?php echo strtolower($item['name']); ?>">
            <td><strong><?php echo sanitize($item['name']); ?></strong><br><span style="font-size:0.78rem;color:var(--text3)"><?php echo substr(sanitize($item['description']),0,60); ?>...</span></td>
            <td><span class="badge badge-info"><?php echo sanitize($item['cat_name']??'—'); ?></span></td>
            <td><strong>₹<?php echo number_format($item['price'],0); ?></strong></td>
            <td><?php echo $item['prep_time']; ?> min</td>
            <td>
              <a href="?toggle=<?php echo $item['id']; ?>" style="text-decoration:none">
                <span class="badge badge-<?php echo $item['is_available']?'success':'danger'; ?>" style="cursor:pointer">
                  <?php echo $item['is_available']?'✅ Available':'❌ Hidden'; ?>
                </span>
              </a>
            </td>
            <td><?php echo $item['is_popular']?'⭐':'—'; ?></td>
            <td>
              <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" onclick="openEdit(<?php echo htmlspecialchars(json_encode($item)); ?>);return false;">Edit</a>
              <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Hide this item?')">Hide</a>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalTitle">Add Menu Item</h3>
      <button class="modal-close" onclick="closeAddModal()">✕</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="id" id="editId" value="0">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Item Name *</label>
            <input type="text" name="name" id="editName" class="form-control" required placeholder="Butter Chicken">
          </div>
          <div class="form-group">
            <label class="form-label">Price (₹) *</label>
            <input type="number" name="price" id="editPrice" class="form-control" required step="0.01" min="1" placeholder="380">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" id="editDesc" class="form-control" rows="2" placeholder="Describe the dish..."></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category_id" id="editCat" class="form-control">
              <option value="">— Select —</option>
              <?php foreach($catArr as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon'].' '.$cat['name']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Prep Time (min)</label>
            <input type="number" name="prep_time" id="editPrep" class="form-control" value="15" min="1" max="120">
          </div>
        </div>
        <div style="display:flex;gap:20px;margin-top:8px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem">
            <input type="checkbox" name="is_available" id="editAvail" checked style="accent-color:var(--primary)">
            Available on menu
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem">
            <input type="checkbox" name="is_popular" id="editPop" style="accent-color:var(--primary)">
            ⭐ Mark as Popular
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">Add Item</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(item) {
  document.getElementById('modalTitle').textContent = 'Edit Item';
  document.getElementById('submitBtn').textContent = 'Save Changes';
  document.getElementById('editId').value = item.id;
  document.getElementById('editName').value = item.name;
  document.getElementById('editPrice').value = item.price;
  document.getElementById('editDesc').value = item.description || '';
  document.getElementById('editCat').value = item.category_id || '';
  document.getElementById('editPrep').value = item.prep_time || 15;
  document.getElementById('editAvail').checked = item.is_available == 1;
  document.getElementById('editPop').checked = item.is_popular == 1;
  document.getElementById('addModal').classList.add('open');
}
function closeAddModal() {
  document.getElementById('addModal').classList.remove('open');
  document.getElementById('editId').value = '0';
  document.getElementById('modalTitle').textContent = 'Add Menu Item';
  document.getElementById('submitBtn').textContent = 'Add Item';
}
function filterTable(q) {
  document.querySelectorAll('#menuTable tbody tr').forEach(r => {
    r.style.display = r.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
  });
}
<?php if($editItem): ?>
openEdit(<?php echo json_encode($editItem); ?>);
<?php endif; ?>
</script>
</body>
</html>
