// EatSmart — Main JavaScript

// ── Cart State ──
const Cart = {
  items: JSON.parse(localStorage.getItem('es_cart') || '[]'),

  save() { localStorage.setItem('es_cart', JSON.stringify(this.items)); },

  add(id, name, price, emoji = '🍽️') {
    const existing = this.items.find(i => i.id === id);
    if (existing) { existing.qty++; }
    else { this.items.push({ id, name, price: parseFloat(price), emoji, qty: 1 }); }
    this.save(); this.render(); this.showNotif(name);
  },

  remove(id) {
    this.items = this.items.filter(i => i.id !== id);
    this.save(); this.render();
  },

  updateQty(id, delta) {
    const item = this.items.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) this.remove(id);
    else { this.save(); this.render(); }
  },

  clear() { this.items = []; this.save(); this.render(); },

  total() { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },
  count() { return this.items.reduce((s, i) => s + i.qty, 0); },

  render() {
    const count = this.count();
    document.querySelectorAll('.cart-count').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'flex' : 'none';
    });
    const itemsEl = document.getElementById('cartItems');
    if (!itemsEl) return;
    if (this.items.length === 0) {
      itemsEl.innerHTML = '<div class="empty-state"><div class="icon">🛒</div><p>Your cart is empty</p></div>';
    } else {
      itemsEl.innerHTML = this.items.map(item => `
        <div class="cart-item">
          <div class="cart-item-img">${item.emoji}</div>
          <div class="cart-item-info">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">₹${(item.price * item.qty).toFixed(0)}</div>
          </div>
          <div class="qty-control">
            <button class="qty-btn" onclick="Cart.updateQty(${item.id}, -1)">−</button>
            <span class="qty-num">${item.qty}</span>
            <button class="qty-btn" onclick="Cart.updateQty(${item.id}, 1)">+</button>
          </div>
        </div>
      `).join('');
    }
    const subtotal = this.total();
    const tax = subtotal * 0.05;
    const grand = subtotal + tax;
    const el = id => document.getElementById(id);
    if (el('cartSubtotal')) el('cartSubtotal').textContent = '₹' + subtotal.toFixed(0);
    if (el('cartTax')) el('cartTax').textContent = '₹' + tax.toFixed(0);
    if (el('cartTotal')) el('cartTotal').textContent = '₹' + grand.toFixed(0);
  },

  showNotif(name) {
    const n = document.createElement('div');
    n.style.cssText = `position:fixed;bottom:90px;right:28px;background:#1A1108;color:white;padding:10px 18px;border-radius:8px;font-size:0.85rem;z-index:9999;animation:fadeIn 0.3s ease;box-shadow:0 4px 20px rgba(0,0,0,0.2)`;
    n.textContent = '🛒 ' + name + ' added!';
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 2200);
  }
};

// ── Cart Sidebar ──
function openCart() {
  document.getElementById('cartOverlay')?.classList.add('open');
  document.getElementById('cartSidebar')?.classList.add('open');
  Cart.render();
}
function closeCart() {
  document.getElementById('cartOverlay')?.classList.remove('open');
  document.getElementById('cartSidebar')?.classList.remove('open');
}

// ── Chatbot ──
const Chatbot = {
  open: false,
  rules: [
    { patterns: ['hello','hi','hey','namaste'], reply: 'Hello! 👋 Welcome to EatSmart. How can I help you today? You can ask about our menu, reservations, or orders.' },
    { patterns: ['menu','food','dishes','eat','hungry'], reply: '🍛 Our menu has Starters, Main Course, Breads, Desserts, Beverages & Biryani. <a href="menu.php" style="color:var(--primary)">View full menu →</a>' },
    { patterns: ['popular','best','recommend','special'], reply: '⭐ Our most loved dishes are: Butter Chicken, Chicken Biryani, Paneer Tikka, Dal Makhani and Mango Lassi!' },
    { patterns: ['reserve','book','table','reservation','slot'], reply: '📅 You can book a table online! <a href="reservation.php" style="color:var(--primary)">Make a reservation →</a> We have tables for 2 to 8 guests.' },
    { patterns: ['order','preorder','pre-order'], reply: '🍽️ You can pre-order your food before arrival so it\'s ready when you get here. <a href="menu.php" style="color:var(--primary)">Order now →</a>' },
    { patterns: ['deal','discount','waste','offer','cheap'], reply: '💚 Check our Waste Reduction Deals — surplus food at big discounts! <a href="waste_deals.php" style="color:var(--primary)">See deals →</a>' },
    { patterns: ['status','track','where','my order'], reply: '📦 You can track your live order status in your <a href="my_orders.php" style="color:var(--primary)">My Orders</a> page.' },
    { patterns: ['time','open','hours','when'], reply: '⏰ We are open daily from 11:00 AM to 11:00 PM. Kitchen closes at 10:30 PM.' },
    { patterns: ['price','cost','how much','expensive'], reply: '💰 Our dishes range from ₹40 (Roti) to ₹480 (Mutton Rogan Josh). Most mains are ₹280-₹420.' },
    { patterns: ['veg','vegetarian','vegan','no meat'], reply: '🥗 We have excellent vegetarian options: Paneer Tikka, Dal Makhani, Paneer Butter Masala, Palak Paneer, Veg Biryani and more!' },
    { patterns: ['spicy','spice','mild'], reply: '🌶️ Our kitchen can adjust spice levels. Just mention your preference in the special instructions when ordering!' },
    { patterns: ['pay','payment','cash','upi','card'], reply: '💳 We accept Cash, UPI, Debit & Credit Cards. Payment is at the restaurant or online checkout.' },
    { patterns: ['cancel','refund'], reply: 'To cancel an order or reservation, please go to <a href="my_orders.php" style="color:var(--primary)">My Orders</a> or <a href="my_reservations.php" style="color:var(--primary)">My Reservations</a>.' },
    { patterns: ['wifi','parking'], reply: '📶 Yes, we have free WiFi! Parking is available in the adjacent lot.' },
    { patterns: ['contact','phone','call','address'], reply: '📍 EatSmart, 42 Food Street, New Delhi. 📞 +91 98765 43210. 📧 hello@eatsmart.com' },
    { patterns: ['thank','thanks','bye','goodbye'], reply: 'Thank you for visiting EatSmart! 🙏 Enjoy your meal! Come back soon 😊' },
  ],
  getReply(msg) {
    const lower = msg.toLowerCase();
    for (const rule of this.rules) {
      if (rule.patterns.some(p => lower.includes(p))) return rule.reply;
    }
    return "I'm not sure about that, but our staff will be happy to help! You can also browse the <a href='menu.php' style='color:var(--primary)'>menu</a> or <a href='reservation.php' style='color:var(--primary)'>make a reservation</a>.";
  },
  send(msg) {
    if (!msg.trim()) return;
    this.appendMsg(msg, 'user');
    const input = document.getElementById('chatInput');
    if (input) input.value = '';
    setTimeout(() => this.appendMsg(this.getReply(msg), 'bot'), 500);
  },
  appendMsg(text, type) {
    const msgs = document.getElementById('chatMsgs');
    if (!msgs) return;
    const div = document.createElement('div');
    div.className = `chat-msg ${type}`;
    div.innerHTML = `<div class="chat-bubble">${text}</div>`;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  },
  toggle() {
    this.open = !this.open;
    document.getElementById('chatWindow')?.classList.toggle('open', this.open);
    if (this.open && document.getElementById('chatMsgs')?.children.length === 0) {
      setTimeout(() => this.appendMsg('Hi! I\'m the EatSmart assistant 🍽️ Ask me about our menu, reservations, deals or anything else!', 'bot'), 300);
    }
  }
};

// ── Navbar Mobile Toggle ──
function toggleNav() {
  document.querySelector('.navbar-nav')?.classList.toggle('show');
}

// ── Time Slot Selection ──
function selectSlot(el) {
  if (el.classList.contains('taken')) return;
  document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
  el.classList.add('selected');
  const input = document.getElementById('selectedSlot');
  if (input) input.value = el.dataset.slot;
}

// ── Tab System ──
function switchTab(tabId, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(tabId)?.classList.add('active');
  btn.classList.add('active');
}

// ── Toast Notification ──
function showToast(msg, type = 'success') {
  const colors = { success: '#2D7A3A', danger: '#C0392B', info: '#1565C0', warning: '#B06A00' };
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;top:88px;right:20px;background:${colors[type]};color:white;padding:12px 20px;border-radius:8px;font-size:0.88rem;z-index:9999;animation:fadeIn 0.3s ease;box-shadow:0 4px 20px rgba(0,0,0,0.15);max-width:320px`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ── Countdown Timer for Deals ──
function startDealTimers() {
  document.querySelectorAll('[data-expires]').forEach(el => {
    function update() {
      const diff = new Date(el.dataset.expires) - new Date();
      if (diff <= 0) { el.textContent = 'Expired'; return; }
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.textContent = `⏱ Expires in ${h}h ${m}m ${s}s`;
    }
    update(); setInterval(update, 1000);
  });
}

// ── Order Status Auto-Refresh ──
function startOrderRefresh(orderId) {
  if (!orderId) return;
  setInterval(async () => {
    try {
      const res = await fetch(`php/get_order_status.php?id=${orderId}`);
      const data = await res.json();
      if (data.status) updateOrderTracker(data.status);
    } catch(e) {}
  }, 8000);
}

function updateOrderTracker(status) {
  const steps = ['pending','accepted','cooking','ready','delivered'];
  const idx = steps.indexOf(status);
  document.querySelectorAll('.tracker-step').forEach((el, i) => {
    el.classList.remove('active','done');
    if (i < idx) el.classList.add('done');
    else if (i === idx) el.classList.add('active');
  });
}

// ── AJAX helpers ──
async function postForm(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  return res.json();
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
  Cart.render();
  startDealTimers();

  // Chat enter key
  document.getElementById('chatInput')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') Chatbot.send(e.target.value);
  });

  // Cart overlay click
  document.getElementById('cartOverlay')?.addEventListener('click', closeCart);

  // Smooth number animation for stats
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString();
      if (current >= target) clearInterval(timer);
    }, 30);
  });
});
