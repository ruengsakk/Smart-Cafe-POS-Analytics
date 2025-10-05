// POS System JavaScript
class POSSystem {
    constructor() {
        this.cart = [];
        this.totalAmount = 0;
        this.init();
    }

    init() {
        this.loadCategories();
        this.loadMenuItems();
        this.loadCustomers();
        this.bindEvents();
    }

    // Load categories from database
    async loadCategories() {
        try {
            const response = await fetch('api/get_categories.php');
            const categories = await response.json();

            const categoryContainer = document.getElementById('categoryButtons');
            categoryContainer.innerHTML = '<button class="btn btn-outline-secondary category-btn active" data-category="all">ทั้งหมด</button>';

            categories.forEach(category => {
                categoryContainer.innerHTML += `
                    <button class="btn btn-outline-secondary category-btn" data-category="${category.id}">
                        ${category.name}
                    </button>
                `;
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load menu items from database
    async loadMenuItems(categoryId = null) {
        try {
            const url = categoryId ? `api/get_menus.php?category=${categoryId}` : 'api/get_menus.php';
            const response = await fetch(url);
            const menus = await response.json();

            const menuContainer = document.getElementById('menuContainer');
            menuContainer.innerHTML = '';

            menus.forEach(menu => {
                const icon = this.getMenuIcon(menu.name, menu.category_name);
                menuContainer.innerHTML += `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card menu-card" onclick="pos.addToCart(${menu.id}, '${menu.name}', ${menu.price})">
                            <div class="card-body d-flex flex-column align-items-center text-center">
                                <i class="${icon} menu-icon"></i>
                                <h5 class="card-title mb-2">${menu.name}</h5>
                                <p class="card-text text-muted mb-3">${menu.category_name || 'เมนูพิเศษ'}</p>
                                <div class="menu-price mb-3">฿${parseFloat(menu.price).toFixed(2)}</div>
                                <button class="btn btn-coffee btn-sm mt-auto w-100">
                                    <i class="fas fa-cart-plus"></i> เพิ่มในตะกร้า
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (error) {
            console.error('Error loading menu items:', error);
        }
    }

    // Load customers from database
    async loadCustomers() {
        try {
            const response = await fetch('api/get_customers.php');
            const customers = await response.json();

            const customerSelect = document.getElementById('customerSelect');
            customerSelect.innerHTML = '<option value="">ลูกค้าทั่วไป</option>';

            customers.forEach(customer => {
                customerSelect.innerHTML += `
                    <option value="${customer.id}">${customer.name} (${customer.phone})</option>
                `;
            });
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    // Add item to cart
    addToCart(id, name, price) {
        const existingItem = this.cart.find(item => item.id === id);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: id,
                name: name,
                price: parseFloat(price),
                quantity: 1
            });
        }

        this.updateCartDisplay();
        this.updateTotal();
    }

    // Remove item from cart
    removeFromCart(id) {
        this.cart = this.cart.filter(item => item.id !== id);
        this.updateCartDisplay();
        this.updateTotal();
    }

    // Update quantity
    updateQuantity(id, quantity) {
        const item = this.cart.find(item => item.id === id);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(id);
            } else {
                item.quantity = quantity;
                this.updateCartDisplay();
                this.updateTotal();
            }
        }
    }

    // Update cart display
    updateCartDisplay() {
        const orderItems = document.getElementById('orderItems');

        if (this.cart.length === 0) {
            orderItems.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>ยังไม่มีรายการสั่งซื้อ</p>
                </div>
            `;
            document.getElementById('processOrder').disabled = true;
        } else {
            let cartHTML = '';
            this.cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                cartHTML += `
                    <div class="order-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${item.name}</strong><br>
                                <small class="text-muted">฿${item.price.toFixed(2)} x ${item.quantity}</small>
                            </div>
                            <div class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" onclick="pos.updateQuantity(${item.id}, ${item.quantity - 1})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn btn-outline-secondary">${item.quantity}</span>
                                    <button class="btn btn-outline-secondary" onclick="pos.updateQuantity(${item.id}, ${item.quantity + 1})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="mt-1">
                                    <strong>฿${subtotal.toFixed(2)}</strong>
                                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="pos.removeFromCart(${item.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            orderItems.innerHTML = cartHTML;
            document.getElementById('processOrder').disabled = false;
        }
    }

    // Update total amount
    updateTotal() {
        this.totalAmount = this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        document.getElementById('totalDisplay').innerHTML = `รวม: ฿${this.totalAmount.toFixed(2)}`;
    }

    // Clear cart
    clearCart() {
        this.cart = [];
        this.totalAmount = 0;
        this.updateCartDisplay();
        this.updateTotal();
    }

    // Process order
    async processOrder() {
        if (this.cart.length === 0) return;

        const customerId = document.getElementById('customerSelect').value;
        const paymentMethod = document.getElementById('paymentMethod').value;

        const orderData = {
            items: this.cart,
            customer_id: customerId || null,
            payment_method: paymentMethod,
            total_amount: this.totalAmount
        };

        try {
            // Show loading indicator
            const processBtn = document.getElementById('processOrder');
            const originalText = processBtn.innerHTML;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...';
            processBtn.disabled = true;

            const response = await fetch('api/process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Get response text to see what we actually received
                const responseText = await response.text();
                console.error('Non-JSON response received:', responseText);
                throw new Error('Server returned non-JSON response. Check server logs.');
            }

            const result = await response.json();

            // Restore button
            processBtn.innerHTML = originalText;
            processBtn.disabled = false;

            if (result.success) {
                this.showReceipt(result.order_id, orderData);
                this.clearCart();

                // Show success message
                this.showNotification('success', 'ออเดอร์ประมวลผลสำเร็จ!');
            } else {
                this.showNotification('error', 'เกิดข้อผิดพลาด: ' + result.message);
            }
        } catch (error) {
            // Restore button
            const processBtn = document.getElementById('processOrder');
            processBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> ประมวลผลออเดอร์';
            processBtn.disabled = false;

            console.error('Error processing order:', error);

            let errorMessage = 'เกิดข้อผิดพลาดในการประมวลผลคำสั่งซื้อ';
            if (error.message.includes('JSON')) {
                errorMessage += '\n\nกรุณาตรวจสอบ:\n- Database connection\n- PHP errors in server logs';
            }

            this.showNotification('error', errorMessage);
        }
    }

    // Show receipt
    showReceipt(orderId, orderData) {
        const now = new Date();
        const receiptContent = document.getElementById('receiptContent');

        let itemsHTML = '';
        orderData.items.forEach(item => {
            const subtotal = item.price * item.quantity;
            itemsHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">฿${item.price.toFixed(2)}</td>
                    <td class="text-end">฿${subtotal.toFixed(2)}</td>
                </tr>
            `;
        });

        receiptContent.innerHTML = `
            <div class="text-center mb-4">
                <h3>☕ Coffee Shop</h3>
                <p>ใบเสร็จรับเงิน</p>
                <hr>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <strong>หมายเลขออเดอร์:</strong> ${orderId}
                </div>
                <div class="col-6 text-end">
                    <strong>วันที่:</strong> ${now.toLocaleDateString('th-TH')}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <strong>เวลา:</strong> ${now.toLocaleTimeString('th-TH')}
                </div>
                <div class="col-6 text-end">
                    <strong>การชำระ:</strong> ${this.getPaymentMethodText(orderData.payment_method)}
                </div>
            </div>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>รายการ</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-end">ราคา</th>
                        <th class="text-end">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHTML}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">รวมทั้งสิ้น:</th>
                        <th class="text-end">฿${orderData.total_amount.toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="text-center mt-4">
                <p>ขอบคุณที่ใช้บริการ</p>
                <small>กรุณาเก็บใบเสร็จไว้เป็นหลักฐาน</small>
            </div>
        `;

        const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        receiptModal.show();
    }

    // Get payment method text
    getPaymentMethodText(method) {
        const methods = {
            'cash': 'เงินสด',
            'qr': 'QR Code',
            'online': 'Online Payment'
        };
        return methods[method] || method;
    }

    // Show notification
    showNotification(type, message) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.pos-notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `pos-notification alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${message.replace(/\n/g, '<br>')}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-hide after 5 seconds for success, 10 seconds for error
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, type === 'success' ? 5000 : 10000);
    }

    // Bind events
    bindEvents() {
        // Category filter
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('category-btn')) {
                // Remove active class from all buttons
                document.querySelectorAll('.category-btn').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked button
                e.target.classList.add('active');

                // Load items for selected category
                const categoryId = e.target.dataset.category;
                if (categoryId === 'all') {
                    this.loadMenuItems();
                } else {
                    this.loadMenuItems(categoryId);
                }
            }
        });

        // Process order button
        document.getElementById('processOrder').addEventListener('click', () => {
            this.processOrder();
        });

        // Clear order button
        document.getElementById('clearOrder').addEventListener('click', () => {
            if (confirm('ต้องการล้างรายการทั้งหมดหรือไม่?')) {
                this.clearCart();
            }
        });
    }

    // Get menu icon based on name and category
    getMenuIcon(name, category) {
        name = name.toLowerCase();
        category = category ? category.toLowerCase() : '';

        // Coffee drinks
        if (name.includes('americano')) return 'fas fa-mug-hot';
        if (name.includes('cappuccino')) return 'fas fa-coffee';
        if (name.includes('latte')) return 'fas fa-coffee';
        if (name.includes('espresso')) return 'fas fa-mug-hot';
        if (name.includes('mocha')) return 'fas fa-mug-saucer';

        // Cold drinks
        if (name.includes('ice') || name.includes('iced') || name.includes('frappe')) return 'fas fa-glass-water';
        if (name.includes('tea') || name.includes('ชา')) return 'fas fa-leaf';
        if (name.includes('chocolate')) return 'fas fa-seedling';
        if (name.includes('น้ำ')) return 'fas fa-glass-water-droplet';

        // Food
        if (name.includes('sandwich')) return 'fas fa-burger';
        if (name.includes('croissant') || name.includes('bread')) return 'fas fa-bread-slice';
        if (name.includes('cake') || name.includes('เค้ก')) return 'fas fa-cake-candles';
        if (name.includes('brownie') || name.includes('cookie')) return 'fas fa-cookie';

        // Category-based fallback
        if (category.includes('ร้อน')) return 'fas fa-mug-hot';
        if (category.includes('เย็น')) return 'fas fa-glass-water';
        if (category.includes('อาหาร')) return 'fas fa-utensils';
        if (category.includes('หวาน')) return 'fas fa-ice-cream';

        // Default
        return 'fas fa-coffee';
    }
}

// Print receipt function
function printReceipt() {
    const receiptContent = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>ใบเสร็จ</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    .text-center { text-align: center; }
                    .text-end { text-align: right; }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

// Initialize POS system
const pos = new POSSystem();