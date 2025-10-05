// Customer Management JavaScript
class CustomerManagement {
    constructor() {
        this.customers = [];
        this.filteredCustomers = [];
        this.deleteId = null;
        this.init();
    }

    init() {
        this.loadCustomers();
    }

    // Load all customers
    async loadCustomers() {
        try {
            const response = await fetch('api/manage_customer.php');
            const result = await response.json();

            if (result.success) {
                this.customers = result.data;
                this.filteredCustomers = [...this.customers];
                this.displayCustomers();
                this.updateStats();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    }

    // Update statistics
    updateStats() {
        const activeCustomers = this.customers.filter(c => c.is_active == 1);
        const vipCustomers = activeCustomers.filter(c => c.tier === 'VIP');
        const totalSpent = activeCustomers.reduce((sum, c) => sum + parseFloat(c.total_spent || 0), 0);
        const totalPoints = activeCustomers.reduce((sum, c) => sum + parseInt(c.points || 0), 0);

        document.getElementById('totalCustomers').textContent = activeCustomers.length;
        document.getElementById('vipCustomers').textContent = vipCustomers.length;
        document.getElementById('totalSpent').textContent = '฿' + totalSpent.toFixed(2);
        document.getElementById('totalPoints').textContent = totalPoints.toLocaleString();
    }

    // Display customers in table
    displayCustomers() {
        const tbody = document.getElementById('customerTableBody');

        if (this.filteredCustomers.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        ไม่พบข้อมูลลูกค้า
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.filteredCustomers.map(customer => `
            <tr>
                <td>${customer.id}</td>
                <td><strong>${customer.name}</strong></td>
                <td>${customer.phone}</td>
                <td>${customer.points || 0}</td>
                <td>฿${parseFloat(customer.total_spent || 0).toFixed(2)}</td>
                <td>${customer.visit_count || 0}</td>
                <td>
                    <span class="tier-badge tier-${customer.tier.toLowerCase()}">
                        ${customer.tier}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${customer.is_active == 1 ? 'status-active' : 'status-inactive'}">
                        ${customer.is_active == 1 ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="customerManagement.editCustomer(${customer.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="customerManagement.deleteCustomer(${customer.id}, '${customer.name}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Search customers
    searchCustomers() {
        const searchTerm = document.getElementById('searchCustomer').value.toLowerCase();
        this.filteredCustomers = this.customers.filter(customer =>
            customer.name.toLowerCase().includes(searchTerm) ||
            customer.phone.includes(searchTerm)
        );
        this.filterCustomers();
    }

    // Filter customers by tier and status
    filterCustomers() {
        const tierFilter = document.getElementById('filterTier').value;
        const statusFilter = document.getElementById('filterStatus').value;
        const searchTerm = document.getElementById('searchCustomer').value.toLowerCase();

        this.filteredCustomers = this.customers.filter(customer => {
            const matchSearch = customer.name.toLowerCase().includes(searchTerm) ||
                               customer.phone.includes(searchTerm);
            const matchTier = !tierFilter || customer.tier === tierFilter;
            const matchStatus = statusFilter === '' || customer.is_active == statusFilter;

            return matchSearch && matchTier && matchStatus;
        });

        this.displayCustomers();
    }

    // Reset filters
    resetFilters() {
        document.getElementById('searchCustomer').value = '';
        document.getElementById('filterTier').value = '';
        document.getElementById('filterStatus').value = '';
        this.filteredCustomers = [...this.customers];
        this.displayCustomers();
    }

    // Open add customer modal
    openCustomerModal() {
        document.getElementById('customerModalTitle').textContent = 'เพิ่มลูกค้าใหม่';
        document.getElementById('customerForm').reset();
        document.getElementById('customerId').value = '';
        document.getElementById('customerPoints').value = '0';
        document.getElementById('customerStatus').checked = true;
    }

    // Edit customer
    async editCustomer(id) {
        try {
            const response = await fetch(`api/manage_customer.php?id=${id}`);
            const result = await response.json();

            if (result.success) {
                const customer = result.data;

                document.getElementById('customerModalTitle').textContent = 'แก้ไขข้อมูลลูกค้า';
                document.getElementById('customerId').value = customer.id;
                document.getElementById('customerName').value = customer.name;
                document.getElementById('customerPhone').value = customer.phone;
                document.getElementById('customerPoints').value = customer.points || 0;
                document.getElementById('customerStatus').checked = customer.is_active == 1;

                const modal = new bootstrap.Modal(document.getElementById('customerModal'));
                modal.show();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading customer:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    }

    // Save customer (add or update)
    async saveCustomer() {
        const id = document.getElementById('customerId').value;
        const name = document.getElementById('customerName').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();
        const points = document.getElementById('customerPoints').value;
        const is_active = document.getElementById('customerStatus').checked ? 1 : 0;

        // Validate
        if (!name || !phone) {
            this.showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
            return;
        }

        // Validate phone number (10 digits)
        if (!/^[0-9]{10}$/.test(phone)) {
            this.showNotification('กรุณากรอกเบอร์โทรศัพท์ 10 หลัก', 'error');
            return;
        }

        const data = {
            name: name,
            phone: phone,
            points: parseInt(points) || 0,
            is_active: is_active
        };

        try {
            let url = 'api/manage_customer.php';
            let method = 'POST';

            if (id) {
                // Update
                data.id = parseInt(id);
                method = 'PUT';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(id ? 'แก้ไขข้อมูลสำเร็จ' : 'เพิ่มลูกค้าสำเร็จ', 'success');
                bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
                this.loadCustomers();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error saving customer:', error);
            this.showNotification('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }
    }

    // Delete customer
    deleteCustomer(id, name) {
        this.deleteId = id;
        document.getElementById('deleteCustomerName').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Confirm delete
    async confirmDelete() {
        if (!this.deleteId) return;

        try {
            const response = await fetch(`api/manage_customer.php?id=${this.deleteId}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('ลบลูกค้าสำเร็จ', 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                this.deleteId = null;
                this.loadCustomers();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error deleting customer:', error);
            this.showNotification('เกิดข้อผิดพลาดในการลบ', 'error');
        }
    }

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
}

// Initialize
const customerManagement = new CustomerManagement();

// Global functions for onclick events
function openCustomerModal() {
    customerManagement.openCustomerModal();
}

function saveCustomer() {
    customerManagement.saveCustomer();
}

function searchCustomers() {
    customerManagement.searchCustomers();
}

function filterCustomers() {
    customerManagement.filterCustomers();
}

function resetFilters() {
    customerManagement.resetFilters();
}

function confirmDelete() {
    customerManagement.confirmDelete();
}
