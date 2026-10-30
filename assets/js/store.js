/**
 * Văn Trường Luxury Mobile - Frontend Client Store (100% HTML/JS)
 * Quản lý Giỏ hàng, Tài khoản, Mã ưu đãi và Đơn hàng lưu tại localStorage
 */

const Store = (() => {
  const CART_KEY = 'vantruong_cart';
  const USER_KEY = 'vantruong_user';
  const ORDERS_KEY = 'vantruong_orders';
  const COUPON_KEY = 'vantruong_coupon';

  // Default seed products in cart if first time visiting
  const defaultCart = [
    {
      id: 'ip16pm-256-desert',
      productId: 4,
      name: 'iPhone 16 Pro Max 256GB Titan Sa Mạc',
      color: 'Titan Sa Mạc',
      storage: '256GB',
      condition: 'VN/A Mới 100%',
      price: 34990000,
      oldPrice: 37990000,
      quantity: 1,
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC3qD7IIjaaeWVNu5_UrVc5RAtmCQnep7SNBpZIbYzjroNeNNBh1BAm_qvPny6IZXE1YDKSsAMYCmp-LHeKCA-RiuLX1kxjwz58wndVL2IrM-IlSlKaBYDRAmRZgwMWX2enRMY7piJT_pqEugY4MkQrwMG5ojAV2e3PWmYzhqtxGsAoSh2oLWq849d88Wx3wtB45-gINIo4Hrz2IC2REOyGVv9vWkGtfUV4BDQ1yX4'
    },
    {
      id: 'charger-20w',
      productId: 99,
      name: 'Củ sạc nhanh Apple USB-C 20W Chính Hãng',
      color: 'Trắng',
      storage: '20W PD',
      condition: 'Chính hãng Apple',
      price: 490000,
      oldPrice: 590000,
      quantity: 1,
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDAjm4Z8NMGCwxjt_rxm_h4sTMNZm20HkezoeVXopQmQsHrSKLbQ5p48cVWkL0peNcq9_3ozdHR4Sg83lRUIitvSIZgnsffAiolrpATr7XPOnetiAHdUnkxwEnWZUesa0LQjIhkSMaK0S0ujul1lmvydBsrnCgy4stDjCPYGA5s19jqVn-z5tPlydkacPZeJe5-A0zv2NxTqJrw_9q103Z38C3707vVfb4f1nXB2-0'
    }
  ];

  // Default seed user (Lê Văn Trường)
  const defaultUser = {
    userId: 1,
    fullName: 'Lê Văn Trường',
    email: 'truong@example.com',
    phone: '0988 123 456',
    address: '256 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
    birthday: '2002-05-20',
    gender: 'Nam',
    memberTier: 'VT Luxury VIP Diamond',
    points: 2500,
    isLoggedIn: true
  };

  // Valid promo coupons
  const coupons = {
    'TRUONGVIP': { code: 'TRUONGVIP', discount: 500000, minOrder: 15000000, desc: 'Giảm 500.000₫ cho đơn từ 15 triệu' },
    'VTLUXURY10': { code: 'VTLUXURY10', percent: 10, maxDiscount: 1500000, minOrder: 2000000, desc: 'Giảm 10% tối đa 1.500.000₫' },
    'WELCOMEVT': { code: 'WELCOMEVT', discount: 200000, minOrder: 5000000, desc: 'Ưu đãi 200.000₫ cho khách hàng mới' },
    'MINH500': { code: 'MINH500', discount: 500000, minOrder: 15000000, desc: 'Giảm 500.000₫ cho đơn từ 15 triệu (Mã cũ)' }
  };

  // Initialize storage
  if (!localStorage.getItem(CART_KEY)) {
    localStorage.setItem(CART_KEY, JSON.stringify(defaultCart));
  }
  if (!localStorage.getItem(USER_KEY)) {
    localStorage.setItem(USER_KEY, JSON.stringify(defaultUser));
  }

  // Helper formatting
  const formatMoney = (val) => new Intl.NumberFormat('vi-VN').format(Number(val || 0)) + '₫';

  // Toast UI
  const toast = (message, tone = 'success') => {
    let box = document.querySelector('.stitch-toast-container');
    if (!box) {
      box = document.createElement('div');
      box.className = 'stitch-toast-container fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none';
      document.body.appendChild(box);
    }
    const item = document.createElement('div');
    const isError = tone === 'error';
    const bgClass = isError ? 'bg-red-600 text-white' : 'bg-slate-900 text-white';
    const icon = isError ? 'error' : 'check_circle';

    item.className = `${bgClass} px-4 py-3 rounded-2xl shadow-xl flex items-center gap-2 text-sm font-medium transition-all duration-300 transform translate-y-4 opacity-0 pointer-events-auto`;
    item.innerHTML = `
      <span class="material-symbols-outlined text-base">${icon}</span>
      <span>${message}</span>
    `;
    box.appendChild(item);

    requestAnimationFrame(() => {
      item.classList.remove('translate-y-4', 'opacity-0');
    });

    setTimeout(() => {
      item.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => item.remove(), 300);
    }, 2800);
  };

  // Cart API
  const getCart = () => {
    try {
      return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch {
      return [];
    }
  };

  const saveCart = (cart) => {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateBadges();
    window.dispatchEvent(new CustomEvent('cartUpdated', { detail: cart }));
  };

  const getCartCount = () => {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0);
  };

  const addToCart = (product, quantity = 1) => {
    const cart = getCart();
    const existingIndex = cart.findIndex(item => item.id === product.id);

    if (existingIndex > -1) {
      cart[existingIndex].quantity = (Number(cart[existingIndex].quantity) || 1) + Number(quantity);
    } else {
      cart.push({
        ...product,
        quantity: Number(quantity) || 1
      });
    }

    saveCart(cart);
    toast(`Đã thêm "${product.name}" vào giỏ hàng!`);
  };

  const updateQuantity = (id, quantity) => {
    let cart = getCart();
    const qty = parseInt(quantity, 10);
    if (qty <= 0) {
      removeFromCart(id);
      return;
    }
    cart = cart.map(item => item.id === id ? { ...item, quantity: qty } : item);
    saveCart(cart);
  };

  const removeFromCart = (id) => {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    saveCart(cart);
    toast('Đã xóa sản phẩm khỏi giỏ hàng.', 'info');
  };

  const clearCart = () => {
    saveCart([]);
    toast('Đã làm trống giỏ hàng.');
  };

  // Coupons
  const getAppliedCoupon = () => {
    const code = localStorage.getItem(COUPON_KEY);
    return code && coupons[code] ? coupons[code] : null;
  };

  const applyCoupon = (code) => {
    const cleanCode = (code || '').trim().toUpperCase();
    if (!cleanCode) {
      return { success: false, message: 'Vui lòng nhập mã giảm giá.' };
    }
    if (!coupons[cleanCode]) {
      return { success: false, message: 'Mã giảm giá không hợp lệ hoặc đã hết hạn.' };
    }
    const c = coupons[cleanCode];
    const subtotal = getSubtotal();
    if (c.minOrder && subtotal < c.minOrder) {
      return { success: false, message: `Mã ${cleanCode} áp dụng cho đơn từ ${formatMoney(c.minOrder)}.` };
    }
    localStorage.setItem(COUPON_KEY, cleanCode);
    return { success: true, message: `Áp dụng mã ${cleanCode} thành công!`, coupon: c };
  };

  const removeCoupon = () => {
    localStorage.removeItem(COUPON_KEY);
  };

  const calculateDiscount = (subtotal) => {
    const coupon = getAppliedCoupon();
    if (!coupon) return 0;
    if (coupon.discount) return coupon.discount;
    if (coupon.percent) {
      const disc = Math.round((subtotal * coupon.percent) / 100);
      return coupon.maxDiscount ? Math.min(disc, coupon.maxDiscount) : disc;
    }
    return 0;
  };

  const getSubtotal = () => {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + (Number(item.price) * (Number(item.quantity) || 1)), 0);
  };

  const getTotals = () => {
    const subtotal = getSubtotal();
    const discount = calculateDiscount(subtotal);
    const shipping = 0; // Free shipping
    const grandTotal = Math.max(0, subtotal - discount + shipping);
    return { subtotal, discount, shipping, grandTotal };
  };

  // User Auth API
  const getUser = () => {
    try {
      const u = JSON.parse(localStorage.getItem(USER_KEY));
      return u && u.isLoggedIn ? u : null;
    } catch {
      return null;
    }
  };

  const login = (email, password) => {
    // Simulated credential check
    const cleanEmail = (email || '').trim().toLowerCase();
    const user = {
      userId: 1,
      fullName: cleanEmail === 'truong@example.com' || cleanEmail === 'minh@example.com' ? 'Lê Văn Trường' : cleanEmail.split('@')[0],
      email: cleanEmail,
      phone: '0988 123 456',
      address: '256 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
      birthday: '2002-05-20',
      gender: 'Nam',
      memberTier: 'VT Luxury VIP Diamond',
      points: 2500,
      isLoggedIn: true
    };
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    toast('Đăng nhập thành công!');
    return true;
  };

  const register = (data) => {
    const newUser = {
      userId: Date.now(),
      fullName: data.fullName || 'Khách hàng mới',
      email: data.email,
      phone: data.phone || '0988 000 111',
      address: data.address || 'Hà Nội',
      birthday: data.birthday || '2000-01-01',
      gender: data.gender || 'Nam',
      memberTier: 'VT Luxury Member',
      points: 100,
      isLoggedIn: true
    };
    localStorage.setItem(USER_KEY, JSON.stringify(newUser));
    toast('Đăng ký tài khoản thành công!');
    return true;
  };

  const updateUser = (data) => {
    const current = getUser() || defaultUser;
    const updated = { ...current, ...data, isLoggedIn: true };
    localStorage.setItem(USER_KEY, JSON.stringify(updated));
    toast('Đã cập nhật thông tin thành công!');
    return updated;
  };

  const logout = () => {
    const current = getUser() || defaultUser;
    localStorage.setItem(USER_KEY, JSON.stringify({ ...current, isLoggedIn: false }));
    toast('Đã đăng xuất.');
  };

  // Order API
  const getOrders = () => {
    try {
      const orders = JSON.parse(localStorage.getItem(ORDERS_KEY));
      if (orders && Array.isArray(orders)) return orders;
    } catch {}
    // Seed sample orders if none exist
    const sampleOrders = [
      {
        orderCode: 'VT26101501',
        createdAt: '15/10/2026 14:30',
        recipientName: 'Lê Văn Trường',
        phone: '0988 123 456',
        shippingAddress: '256 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
        items: [
          {
            name: 'iPhone 16 Pro Max 256GB Titan Sa Mạc',
            price: 34990000,
            quantity: 1,
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC3qD7IIjaaeWVNu5_UrVc5RAtmCQnep7SNBpZIbYzjroNeNNBh1BAm_qvPny6IZXE1YDKSsAMYCmp-LHeKCA-RiuLX1kxjwz58wndVL2IrM-IlSlKaBYDRAmRZgwMWX2enRMY7piJT_pqEugY4MkQrwMG5ojAV2e3PWmYzhqtxGsAoSh2oLWq849d88Wx3wtB45-gINIo4Hrz2IC2REOyGVv9vWkGtfUV4BDQ1yX4'
          }
        ],
        subtotal: 34990000,
        discount: 500000,
        shippingFee: 0,
        total: 34490000,
        paymentMethod: 'COD',
        paymentStatus: 'Đã thanh toán',
        status: 'Giao hàng thành công',
        note: 'Giao giờ hành chính'
      },
      {
        orderCode: 'VT26101289',
        createdAt: '12/10/2026 09:15',
        recipientName: 'Lê Văn Trường',
        phone: '0988 123 456',
        shippingAddress: '256 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội',
        items: [
          {
            name: 'Tai nghe Apple AirPods Pro 2 MagSafe (USB-C)',
            price: 5490000,
            quantity: 1,
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDAjm4Z8NMGCwxjt_rxm_h4sTMNZm20HkezoeVXopQmQsHrSKLbQ5p48cVWkL0peNcq9_3ozdHR4Sg83lRUIitvSIZgnsffAiolrpATr7XPOnetiAHdUnkxwEnWZUesa0LQjIhkSMaK0S0ujul1lmvydBsrnCgy4stDjCPYGA5s19jqVn-z5tPlydkacPZeJe5-A0zv2NxTqJrw_9q103Z38C3707vVfb4f1nXB2-0'
          }
        ],
        subtotal: 5490000,
        discount: 0,
        shippingFee: 0,
        total: 5490000,
        paymentMethod: 'Chuyển khoản VietQR',
        paymentStatus: 'Đã thanh toán',
        status: 'Giao hàng thành công',
        note: 'Gọi trước khi đến'
      }
    ];
    localStorage.setItem(ORDERS_KEY, JSON.stringify(sampleOrders));
    return sampleOrders;
  };

  const createOrder = (orderData) => {
    const orders = getOrders();
    const now = new Date();
    const code = 'VT' + now.getFullYear().toString().slice(-2) +
      String(now.getMonth() + 1).padStart(2, '0') +
      String(now.getDate()).padStart(2, '0') +
      Math.floor(1000 + Math.random() * 9000);

    const newOrder = {
      orderCode: code,
      createdAt: now.toLocaleString('vi-VN'),
      recipientName: orderData.recipientName,
      phone: orderData.phone,
      shippingAddress: orderData.shippingAddress,
      items: getCart(),
      subtotal: orderData.subtotal,
      discount: orderData.discount,
      shippingFee: 0,
      total: orderData.total,
      paymentMethod: orderData.paymentMethod || 'COD',
      paymentStatus: orderData.paymentMethod === 'COD' ? 'Chưa thanh toán' : 'Đã thanh toán',
      status: 'Chờ xác nhận',
      note: orderData.note || ''
    };

    orders.unshift(newOrder);
    localStorage.setItem(ORDERS_KEY, JSON.stringify(orders));
    // Clear cart and coupon after checkout
    clearCart();
    removeCoupon();
    return newOrder;
  };

  // Header badges update
  const updateBadges = () => {
    const count = getCartCount();
    document.querySelectorAll('#cartCountBadge, .cart-count, [data-cart-count]').forEach(el => {
      el.textContent = count;
      if (count === 0) {
        el.style.display = 'none';
      } else {
        el.style.display = 'flex';
      }
    });

    // Update avatar link based on login state
    const user = getUser();
    document.querySelectorAll('a[href="login.html"]').forEach(link => {
      if (user && user.isLoggedIn) {
        link.setAttribute('href', 'profile.html');
        link.setAttribute('title', `Tài khoản: ${user.fullName}`);
      }
    });
  };

  // Auto-bind product card interactions across all HTML pages
  if (typeof document !== 'undefined') {
    document.addEventListener('click', (e) => {
      // Ignore clicks within navigation, header, drawer, and modals
      if (e.target.closest('#miniCartDrawer, #cartBackdrop, #welcomePromoPopup, nav, header, footer')) {
        return;
      }

      const btn = e.target.closest('button, a');
      if (btn) {
        const text = btn.textContent.trim().toLowerCase();
        const isAddToCart = text.includes('thêm giỏ') || text.includes('thêm vào giỏ');
        const isBuyNow = text.includes('mua ngay');

        if (isAddToCart || isBuyNow) {
          const card = btn.closest('article, .product-card, [class*="group relative"]');
          if (card) {
            const titleEl = card.querySelector('h2, h3, h4, .font-headline-sm');
            const name = titleEl ? titleEl.textContent.trim() : 'iPhone VN/A';

            const imgEl = card.querySelector('img');
            const img = imgEl ? imgEl.src : '';

            const priceEl = card.querySelector('.text-primary, [class*="text-primary"]');
            const priceText = priceEl ? priceEl.textContent : '29990000';
            const price = parseInt(priceText.replace(/\D/g, ''), 10) || 29990000;

            const id = 'prod-' + name.toLowerCase().replace(/[^a-z0-9]+/g, '-');

            e.preventDefault();
            e.stopPropagation();

            addToCart({
              id: id,
              name: name,
              price: price,
              image: img,
              condition: 'Chính hãng VN/A'
            }, 1);

            if (isBuyNow) {
              setTimeout(() => {
                location.href = 'checkout.html';
              }, 250);
            } else if (typeof toggleCartDrawer === 'function') {
              toggleCartDrawer(true);
            }
            return;
          }
        }
      }

      // Check if user clicked on product image or product title inside any product card
      const card = e.target.closest('article, .product-card');
      if (card) {
        const isImg = e.target.closest('img');
        const isTitle = e.target.closest('h2, h3, h4, .font-headline-sm');

        if (isImg || isTitle) {
          const titleEl = card.querySelector('h2, h3, h4, .font-headline-sm');
          const name = titleEl ? titleEl.textContent.trim() : 'iPhone VN/A';

          const imgEl = card.querySelector('img');
          const img = imgEl ? imgEl.src : '';

          const priceEl = card.querySelector('.text-primary, [class*="text-primary"]');
          const priceText = priceEl ? priceEl.textContent : '29990000';
          const price = parseInt(priceText.replace(/\D/g, ''), 10) || 29990000;

          e.preventDefault();
          window.location.href = `product-detail.html?name=${encodeURIComponent(name)}&price=${price}&img=${encodeURIComponent(img)}`;
        }
      }
    });
  }

  return {
    getCart,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    getCartCount,
    getSubtotal,
    getTotals,
    applyCoupon,
    removeCoupon,
    getAppliedCoupon,
    getUser,
    login,
    register,
    updateUser,
    logout,
    getOrders,
    createOrder,
    formatMoney,
    toast,
    updateBadges
  };
})();

window.Store = Store;
