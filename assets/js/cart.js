let currentRestaurantId = null;

// Get current restaurant ID
function getCurrentRestaurantId() {
    // Try from global variable (set in restaurant.php)
    if (typeof window.currentRestaurantId !== 'undefined') {
        return window.currentRestaurantId;
    }
    
    // Try from URL
    const urlParams = new URLSearchParams(window.location.search);
    const idFromUrl = urlParams.get('id');
    if (idFromUrl) return idFromUrl;
    
    // Try from page data attribute
    const restaurantHeader = document.getElementById('restaurantHeader');
    if (restaurantHeader && restaurantHeader.dataset.restaurantId) {
        return restaurantHeader.dataset.restaurantId;
    }
    
    console.warn('Could not determine restaurant ID');
    return null;
}

function checkAndRestoreCart(restaurantId) {
    // Check if we need to restore cart from saved carts
    fetch('check_and_restore_cart.php?restaurant_id=' + restaurantId)
        .then(response => response.json())
        .then(data => {
            if (data.restored) {
                // Cart was restored, update display
                updateCart('view');
            }
        })
        .catch(error => console.error('Error restoring cart:', error));
}
// Modified updateCart function
function updateCart(action, id = null, qty = null, options = {}, deliveryFee = null) {
    // Get current restaurant ID
    const restaurantId = getCurrentRestaurantId();
    
    if (!restaurantId && action !== 'view' && action !== 'clear_all') {
        console.error('Cannot update cart: no restaurant ID');
        return;
    }
    
    // Add restaurant_id to every cart request
    let body = new URLSearchParams();
    body.append('action', action);
    
    // CRITICAL: Always send restaurant_id with cart requests
    if (restaurantId) {
        body.append('restaurant_id', restaurantId);
    }
    
    if (id !== null) body.append('id', id);
    if (qty !== null) body.append('qty', qty);
    if (Object.keys(options).length > 0) {
        body.append('options', JSON.stringify(options));
    }
    if (deliveryFee !== null) {
        body.append('delivery_fee', deliveryFee);
    }

    console.log('Cart request:', action, 'Restaurant:', restaurantId);
    
    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
        const container = document.getElementById('cart-container');
        if (container) {
            container.innerHTML = html;
            
            // Add quantity controls to cart items
            addQuantityControlsToCartItems();
        }
        syncCartBadgeFromCartHtml();
    })
    .catch(error => console.error('Error updating cart:', error));
}

// ===== QUANTITY CONTROLS =====
function addQuantityControlsToCartItems() {
    const cartContainer = document.getElementById('cart-container');
    if (!cartContainer) return;
    
    // Find all cart items
    const cartLines = cartContainer.querySelectorAll('.cart-line');
    
    cartLines.forEach(cartLine => {
        // Skip if already has controls
        if (cartLine.querySelector('.qty-controls')) return;
        
        // Find the remove button to get the key
        const removeBtn = cartLine.querySelector('[onclick^="removeFromCart"]');
        if (!removeBtn) return;
        
        // Extract the cart key
        const onclickText = removeBtn.getAttribute('onclick');
        const match = onclickText.match(/removeFromCart\('([^']+)'\)/);
        if (!match) return;
        
        const cartKey = match[1];
        
        // Find the quantity element
        const qtyElement = cartLine.querySelector('.qty');
        if (!qtyElement) return;
        
        const currentQty = parseInt(qtyElement.textContent) || 1;
        
        // Create quantity controls
        const qtyControls = document.createElement('div');
        qtyControls.className = 'qty-controls';
        
        // Minus button
        const minusBtn = document.createElement('button');
        minusBtn.type = 'button';
        minusBtn.className = 'qty-btn minus';
        minusBtn.innerHTML = '−';
        minusBtn.onclick = function(e) {
            e.stopPropagation();
            const newQty = currentQty - 1;
            if (newQty >= 1) {
                updateQty(cartKey, newQty);
            }
        };
        
        // Quantity display
        const qtySpan = document.createElement('span');
        qtySpan.className = 'qty-display';
        qtySpan.textContent = currentQty;
        
        // Plus button
        const plusBtn = document.createElement('button');
        plusBtn.type = 'button';
        plusBtn.className = 'qty-btn plus';
        plusBtn.innerHTML = '+';
        plusBtn.onclick = function(e) {
            e.stopPropagation();
            updateQty(cartKey, currentQty + 1);
        };
        
        // Add to container
        qtyControls.appendChild(minusBtn);
        qtyControls.appendChild(qtySpan);
        qtyControls.appendChild(plusBtn);
        
        // Insert after quantity element
        qtyElement.parentNode.insertBefore(qtyControls, qtyElement.nextSibling);
        
        // Hide original qty element
        qtyElement.style.display = 'none';
    });
}

// Modified updateQty with restaurant ID
function updateQty(key, qty) {
    if (qty <= 0) {
        removeFromCart(key);
        return;
    }

    const deliveryFee = getCurrentDeliveryFee();
    const restaurantId = getCurrentRestaurantId();

    let body = new URLSearchParams();
    body.append('action', 'update_qty');
    body.append('key', key);
    body.append('qty', qty);
    
    // CRITICAL: Add restaurant_id
    if (restaurantId) {
        body.append('restaurant_id', restaurantId);
    }
    
    if (deliveryFee !== null) body.append('delivery_fee', deliveryFee);

    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
        const container = document.getElementById('cart-container');
        if (container) {
            container.innerHTML = html;
            addQuantityControlsToCartItems();
        }
        syncCartBadgeFromCartHtml();
    })
    .catch(error => console.error('Error updating cart:', error));
}

// Modified removeFromCart with restaurant ID
function removeFromCart(key) {
    const deliveryFee = getCurrentDeliveryFee();
    const restaurantId = getCurrentRestaurantId();

    let body = new URLSearchParams();
    body.append('action', 'remove');
    body.append('key', key);
    
    // CRITICAL: Add restaurant_id
    if (restaurantId) {
        body.append('restaurant_id', restaurantId);
    }
    
    if (deliveryFee !== null) body.append('delivery_fee', deliveryFee);

    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
        const container = document.getElementById('cart-container');
        if (container) {
            container.innerHTML = html;
            addQuantityControlsToCartItems();
        }
        syncCartBadgeFromCartHtml();
    })
    .catch(error => console.error('Error updating cart:', error));
}

// Modified clearCart with restaurant ID
function clearCart() {
    const deliveryFee = getCurrentDeliveryFee();
    const restaurantId = getCurrentRestaurantId();
    
    let body = new URLSearchParams();
    body.append('action', 'clear');
    
    // CRITICAL: Add restaurant_id
    if (restaurantId) {
        body.append('restaurant_id', restaurantId);
    }
    
    if (deliveryFee !== null) body.append('delivery_fee', deliveryFee);
    
    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
        const container = document.getElementById('cart-container');
        if (container) container.innerHTML = html;
        syncCartBadgeFromCartHtml();
    })
    .catch(error => console.error('Error clearing cart:', error));
}

// ===== KEEP ALL YOUR EXISTING MODAL CODE =====
// (Your modal functions remain EXACTLY the same)
const modal = document.getElementById('addToCartModal');
const modalItemName = document.getElementById('modalItemName');
const modalQuantity = document.getElementById('modalQuantity');
const modalTotalPrice = document.getElementById('modalTotalPrice');
const modalDecrementBtn = document.getElementById('modalDecrementBtn');
const modalIncrementBtn = document.getElementById('modalIncrementBtn');
const modalAddToCartBtn = document.getElementById('modalAddToCartBtn');

let currentItemId = null;
let currentItemPrice = 0;
let currentQuantity = 1;
let currentItemOptions = [];
let selectedOptions = {};

function openAddToCartModal(id, name, price, options = []) {
    currentItemId = id;
    currentItemPrice = Number(price) || 0;
    currentQuantity = 1;
    currentItemOptions = Array.isArray(options) ? options : [];
    selectedOptions = {};

    if (modalItemName) modalItemName.textContent = name;
    
    // Render options
    renderOptions();
    
    updateModalDisplay();
    if (modal) modal.classList.add('visible');
}

function closeModal() {
    if (modal) modal.classList.remove('visible');
}

function renderOptions() {
    const container = document.getElementById('modalOptionsContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (currentItemOptions.length === 0) {
        return;
    }
    
    currentItemOptions.forEach(option => {
        const optionGroup = document.createElement('div');
        optionGroup.className = 'option-group';
        
        const title = document.createElement('span');
        title.className = 'option-title';
        title.textContent = option.option_name;
        if (option.is_required == 1) {
            title.innerHTML += ' <span class="option-required">*</span>';
        }
        
        optionGroup.appendChild(title);
        
        if (option.option_type === 'single_select') {
            renderRadioOptions(optionGroup, option);
        } else if (option.option_type === 'multi_select') {
            renderCheckboxOptions(optionGroup, option);
        }
        
        container.appendChild(optionGroup);
    });
}

function renderRadioOptions(container, option) {
    option.values.forEach(value => {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = `option_${option.option_id}`;
        radio.value = value.value_id;
        radio.id = `opt_${option.option_id}_${value.value_id}`;
        radio.addEventListener('change', () => {
            if (option.is_required == 1 || radio.checked) {
                selectedOptions[option.option_id] = radio.checked ? [value] : [];
                updateModalDisplay();
            }
        });
        
        const label = document.createElement('label');
        label.htmlFor = `opt_${option.option_id}_${value.value_id}`;
        label.textContent = value.value_name;
        
        const priceSpan = document.createElement('span');
        priceSpan.className = 'option-price';
        priceSpan.textContent = value.price_modifier > 0 ? `+${value.price_modifier} MMK` : '';
        
        optionItem.appendChild(radio);
        optionItem.appendChild(label);
        optionItem.appendChild(priceSpan);
        container.appendChild(optionItem);
    });
}

function renderCheckboxOptions(container, option) {
    option.values.forEach(value => {
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = `option_${option.option_id}[]`;
        checkbox.value = value.value_id;
        checkbox.id = `opt_${option.option_id}_${value.value_id}`;
        checkbox.addEventListener('change', () => {
            if (!selectedOptions[option.option_id]) {
                selectedOptions[option.option_id] = [];
            }
            
            if (checkbox.checked) {
                selectedOptions[option.option_id].push(value);
            } else {
                selectedOptions[option.option_id] = selectedOptions[option.option_id].filter(
                    item => item.value_id != value.value_id
                );
            }
            
            updateModalDisplay();
        });
        
        const label = document.createElement('label');
        label.htmlFor = `opt_${option.option_id}_${value.value_id}`;
        label.textContent = value.value_name;
        
        const priceSpan = document.createElement('span');
        priceSpan.className = 'option-price';
        priceSpan.textContent = value.price_modifier > 0 ? `+${value.price_modifier} MMK` : '';
        
        optionItem.appendChild(checkbox);
        optionItem.appendChild(label);
        optionItem.appendChild(priceSpan);
        container.appendChild(optionItem);
    });
}

function validateOptions() {
    let isValid = true;
    
    currentItemOptions.forEach(option => {
        if (option.is_required == 1) {
            if (!selectedOptions[option.option_id] || selectedOptions[option.option_id].length === 0) {
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function calculateOptionPrice() {
    let optionTotal = 0;
    
    Object.values(selectedOptions).forEach(optionArray => {
        optionArray.forEach(option => {
            optionTotal += Number(option.price_modifier) || 0;
        });
    });
    
    return optionTotal;
}

function updateModalDisplay() {
    if (modalQuantity) modalQuantity.textContent = currentQuantity;
    
    const optionPrice = calculateOptionPrice();
    const totalPrice = (currentItemPrice + optionPrice) * currentQuantity;
    
    if (modalTotalPrice) modalTotalPrice.textContent = totalPrice.toLocaleString();
    if (modalDecrementBtn) modalDecrementBtn.disabled = currentQuantity <= 1;
    
    const errorElement = document.getElementById('optionError');
    if (errorElement) {
        errorElement.style.display = validateOptions() ? 'none' : 'block';
    }
}

if (modalDecrementBtn) modalDecrementBtn.onclick = () => {
    if (currentQuantity > 1) {
        currentQuantity--;
        updateModalDisplay();
    }
};

if (modalIncrementBtn) modalIncrementBtn.onclick = () => {
    currentQuantity++;
    updateModalDisplay();
};

if (modalAddToCartBtn) modalAddToCartBtn.onclick = () => {
    if (!currentItemId) return;
    
    if (!validateOptions()) {
        document.getElementById('optionError').style.display = 'block';
        return;
    }
    
    // Get current delivery fee from the page
    const feeDisplay = document.getElementById('feeDisplay');
    let deliveryFee = null;
    
    if (feeDisplay && feeDisplay.textContent) {
        const feeText = feeDisplay.textContent.trim();
        if (feeText !== 'Free delivery' && feeText !== 'Fee varies') {
            const feeMatch = feeText.match(/(\d+[\d,]*)/);
            if (feeMatch) {
                deliveryFee = parseInt(feeMatch[1].replace(/,/g, ''));
            }
        }
    }
    
    updateCart('add_with_qty', currentItemId, currentQuantity, selectedOptions, deliveryFee);
    closeModal();
};

// close modal if backdrop clicked
window.addEventListener('click', function(event) {
    if (modal && event.target === modal) closeModal();
});

// ===== KEEP ALL YOUR EXISTING HELPER FUNCTIONS =====
function getCurrentDeliveryFee() {
    const feeEl = document.getElementById('feeDisplay');
    if (!feeEl || !feeEl.textContent) return null;
    const txt = feeEl.textContent.trim();
    if (txt === 'Free delivery' || txt === 'Fee varies') return null;
    const m = txt.match(/(\d+[\d,]*)/);
    if (!m) return null;
    return parseInt(m[1].replace(/,/g,''), 10);
}

function syncCartBadgeFromCartHtml() {
    const container = document.getElementById('cart-container');
    const hidden = container ? container.querySelector('#cart-item-count') : null;
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    let newCount = 0;
    if (hidden && hidden.textContent !== undefined) {
        newCount = parseInt(hidden.textContent, 10) || 0;
    } else if (container) {
        const qtyEls = container.querySelectorAll('.cart-list .qty');
        newCount = Array.from(qtyEls).reduce((s, el) => s + (parseInt(el.textContent, 10) || 0), 0);
    }

    badge.textContent = newCount;
    badge.classList.remove('bump');
    void badge.offsetWidth;
    if (newCount > 0) badge.classList.add('bump');
}

function toggleCart() {
    const container = document.getElementById('cart-container');
    if (!container) return;
    container.classList.toggle('hidden');

    if (!container.classList.contains('hidden')) {
        updateCart('view');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('cart-container');
    if (container) container.classList.add('hidden');

    // Get current restaurant ID and check/restore cart
    const restaurantId = getCurrentRestaurantId();
    if (restaurantId) {
        checkAndRestoreCart(restaurantId);
    }
    
    updateCart('view', null, null, {}, getCurrentDeliveryFee());
    
    // Add quantity controls on load
    setTimeout(addQuantityControlsToCartItems, 500);
});

(function ensureCartIcon() {
    const btn = document.getElementById('floating-cart-btn');
    if (!btn) return;
    const i = btn.querySelector('i');
    const svg = btn.querySelector('#cart-svg-fallback');
    const iWidth = i ? i.getBoundingClientRect().width : 0;
    if (i && iWidth < 6) {
        i.classList.add('fa-failed');
        if (svg) svg.style.display = 'block';
    } else {
        if (svg) svg.style.display = 'none';
    }
})();

// Add CSS for quantity controls
const style = document.createElement('style');
style.textContent = `
    .qty-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 8px 0;
    }
    
    .qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #ff6600;
        color: white;
        border: none;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    
    .qty-btn:hover {
        background: #e55a00;
    }
    
    .qty-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .qty-display {
        font-size: 14px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
    }
`;
document.head.appendChild(style);