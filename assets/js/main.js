const qs = (selector, root = document) => root.querySelector(selector);
const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

const toast = (message, tone = 'success') => {
  let box = qs('.stitch-toast');
  if (!box) {
    box = document.createElement('div');
    box.className = 'stitch-toast';
    document.body.appendChild(box);
  }
  box.textContent = message;
  box.className = `stitch-toast ${tone} show`;
  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => box.classList.remove('show'), 2600);
};

const formatMoney = (value) => `${new Intl.NumberFormat('vi-VN').format(Number(value || 0))}\u0111`;

const refreshCartCount = async () => {
  try {
    const response = await fetch('cart-api.php?action=count', { credentials: 'same-origin' });
    const data = await response.json();
    if (!data.ok) return;
    qsa('#cartCountBadge, .cart-link strong').forEach((badge) => {
      badge.textContent = String(data.count);
    });
  } catch (error) {
    // The counter is a nice-to-have; normal page actions still work without it.
  }
};

qsa('[data-confirm]').forEach((button) => {
  button.addEventListener('click', (event) => {
    const message = button.dataset.confirm || 'Ban chac chan muon thuc hien thao tac nay?';
    if (!confirm(message)) {
      event.preventDefault();
    }
  });
});

const menuToggle = qs('.mobile-menu-toggle');
if (menuToggle) {
  menuToggle.addEventListener('click', () => {
    const open = !document.body.classList.contains('nav-open');
    document.body.classList.toggle('nav-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    qs('.material-symbols-outlined', menuToggle).textContent = open ? 'close' : 'menu';
  });
}

qsa('.primary-nav a, .category-nav a').forEach((link) => {
  link.addEventListener('click', () => document.body.classList.remove('nav-open'));
});

qsa('form').forEach((form) => {
  form.addEventListener('submit', (event) => {
    qsa('.field-error', form).forEach((error) => error.remove());
    const invalid = qsa('[required]', form).find((field) => !String(field.value || '').trim());
    if (invalid) {
      event.preventDefault();
      const error = document.createElement('span');
      error.className = 'field-error';
      error.textContent = 'Vui long nhap thong tin nay.';
      invalid.insertAdjacentElement('afterend', error);
      invalid.focus();
      toast('Vui long kiem tra lai thong tin form.', 'error');
      return;
    }

    const submitter = event.submitter;
    if (submitter && submitter.tagName === 'BUTTON' && submitter.type === 'submit') {
      submitter.dataset.originalText = submitter.textContent.trim();
      submitter.classList.add('is-loading');
      setTimeout(() => submitter.classList.remove('is-loading'), 1800);
    }
  });
});

qsa('[data-demo-form]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;
    event.preventDefault();
    form.reset();
    toast('Da ghi nhan yeu cau. Nhan vien se lien he lai som.');
  });
});

qsa('form').forEach((form) => {
  const actionInput = qs('button[name="action"][value="add_to_cart"]', form);
  if (!actionInput) return;

  form.addEventListener('submit', async (event) => {
    if (event.submitter?.value !== 'add_to_cart') return;
    event.preventDefault();

    const button = event.submitter;
    button.disabled = true;
    try {
      const response = await fetch('cart-api.php', {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
      });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.message || 'Khong the them san pham.');
      qsa('#cartCountBadge, .cart-link strong').forEach((badge) => {
        badge.textContent = String(data.count);
      });
      toast(data.message || 'Da them san pham vao gio hang.');
    } catch (error) {
      toast(error.message || 'Co loi xay ra.', 'error');
    } finally {
      button.disabled = false;
      button.classList.remove('is-loading');
    }
  });
});

qsa('input[name="quantity"]:not([type="hidden"])').forEach((input) => {
  if (input.closest('.quantity-control')) return;
  const control = document.createElement('span');
  control.className = 'quantity-control';
  const minus = document.createElement('button');
  const plus = document.createElement('button');
  minus.type = 'button';
  plus.type = 'button';
  minus.setAttribute('aria-label', 'Giam so luong');
  plus.setAttribute('aria-label', 'Tang so luong');
  minus.textContent = '-';
  plus.textContent = '+';
  input.parentNode.insertBefore(control, input);
  control.append(minus, input, plus);

  const changeBy = (delta) => {
    const min = Number(input.min || 1);
    input.value = Math.max(min, Number(input.value || min) + delta);
    input.dispatchEvent(new Event('change', { bubbles: true }));
  };
  minus.addEventListener('click', () => changeBy(-1));
  plus.addEventListener('click', () => changeBy(1));
});

qsa('.variant-option input[type="radio"]').forEach((radio) => {
  radio.addEventListener('change', () => {
    const form = radio.closest('.premium-order-form');
    const option = radio.closest('.variant-option');
    if (!form || !option) return;

    const price = qs('strong', option)?.textContent?.trim();
    const oldPrice = qs('em', option)?.textContent?.trim();
    const priceBox = qs('.price-box', form);
    if (price && priceBox) qs('strong', priceBox).textContent = price;
    if (priceBox) {
      const oldPriceNode = qs('em', priceBox);
      if (oldPriceNode) oldPriceNode.textContent = oldPrice || '';
    }
    toast('Da chon phien ban moi.');
  });
});

qsa('.gallery-thumbs button').forEach((button) => {
  button.addEventListener('click', () => {
    qsa('.gallery-thumbs button').forEach((item) => item.classList.remove('active'));
    button.classList.add('active');
  });
});

const filterForm = qs('.filters form');
if (filterForm) {
  qsa('select', filterForm).forEach((select) => {
    select.addEventListener('change', () => filterForm.requestSubmit());
  });
}

const orderFilter = qs('.order-filter-form');
if (orderFilter) {
  qs('select[name="status"]', orderFilter)?.addEventListener('change', () => orderFilter.requestSubmit());
}

const checkoutForm = qs('.checkout-form');
const shippingRadios = qsa('input[name="shipping_method"]');
const shippingFeeLabel = qs('[data-checkout-shipping-row] strong');
const grandTotalRow = qs('.summary-grand[data-subtotal]');
const grandTotalLabel = grandTotalRow?.querySelector('strong');

if (checkoutForm && shippingRadios.length && shippingFeeLabel && grandTotalRow && grandTotalLabel) {
  const subtotal = Number(grandTotalRow.dataset.subtotal || 0);
  const renderCheckoutTotal = () => {
    const selected = qs('input[name="shipping_method"]:checked');
    const fee = selected?.value === 'express' ? 30000 : 0;
    shippingFeeLabel.textContent = formatMoney(fee);
    grandTotalLabel.textContent = formatMoney(subtotal + fee);
  };

  shippingRadios.forEach((radio) => radio.addEventListener('change', renderCheckoutTotal));
  renderCheckoutTotal();
}

qsa('.checkout-option').forEach((option) => {
  option.addEventListener('click', () => {
    const input = qs('input', option);
    if (input) input.checked = true;
    input?.dispatchEvent(new Event('change', { bubbles: true }));
  });
});

refreshCartCount();
