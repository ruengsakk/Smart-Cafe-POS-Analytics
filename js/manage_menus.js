// Menu Management JavaScript
class MenuManagement {
    constructor() {
        this.menus = [];
        this.categories = [];
        this.filteredMenus = [];
        this.deleteId = null;
        this.init();
    }

    init() {
        this.loadCategories();
        this.loadMenus();
    }

    // Load categories for filter and dropdown
    async loadCategories() {
        try {
            const response = await fetch('api/get_categories.php');
            this.categories = await response.json();

            // Populate filter dropdown
            const filterCategory = document.getElementById('filterCategory');
            this.categories.forEach(category => {
                filterCategory.innerHTML += `<option value="${category.id}">${category.name}</option>`;
            });

            // Populate modal dropdown
            const menuCategory = document.getElementById('menuCategory');
            this.categories.forEach(category => {
                menuCategory.innerHTML += `<option value="${category.id}">${category.name}</option>`;
            });
        } catch (error) {
            console.error('Error loading categories:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดหมวดหมู่', 'error');
        }
    }

    // Load all menus
    async loadMenus() {
        try {
            const response = await fetch('api/manage_menu.php');
            const result = await response.json();

            if (result.success) {
                this.menus = result.data;
                this.filteredMenus = [...this.menus];
                this.displayMenus();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading menus:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    }

    // Display menus in table
    displayMenus() {
        const tbody = document.getElementById('menuTableBody');

        if (this.filteredMenus.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        ไม่พบข้อมูลเมนู
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.filteredMenus.map(menu => `
            <tr>
                <td>${menu.id}</td>
                <td><strong>${menu.name}</strong></td>
                <td><span class="badge bg-info">${menu.category_name || 'ไม่ระบุ'}</span></td>
                <td>฿${parseFloat(menu.price).toFixed(2)}</td>
                <td>
                    <span class="status-badge ${menu.is_active == 1 ? 'status-active' : 'status-inactive'}">
                        ${menu.is_active == 1 ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="menuManagement.editMenu(${menu.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="menuManagement.deleteMenu(${menu.id}, '${menu.name}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Search menus
    searchMenus() {
        const searchTerm = document.getElementById('searchMenu').value.toLowerCase();
        this.filteredMenus = this.menus.filter(menu =>
            menu.name.toLowerCase().includes(searchTerm)
        );
        this.filterMenus();
    }

    // Filter menus by category and status
    filterMenus() {
        const categoryFilter = document.getElementById('filterCategory').value;
        const statusFilter = document.getElementById('filterStatus').value;
        const searchTerm = document.getElementById('searchMenu').value.toLowerCase();

        this.filteredMenus = this.menus.filter(menu => {
            const matchSearch = menu.name.toLowerCase().includes(searchTerm);
            const matchCategory = !categoryFilter || menu.category_id == categoryFilter;
            const matchStatus = statusFilter === '' || menu.is_active == statusFilter;

            return matchSearch && matchCategory && matchStatus;
        });

        this.displayMenus();
    }

    // Reset filters
    resetFilters() {
        document.getElementById('searchMenu').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterStatus').value = '';
        this.filteredMenus = [...this.menus];
        this.displayMenus();
    }

    // Open add menu modal
    openMenuModal() {
        document.getElementById('menuModalTitle').textContent = 'เพิ่มเมนูใหม่';
        document.getElementById('menuForm').reset();
        document.getElementById('menuId').value = '';
        document.getElementById('menuStatus').checked = true;
    }

    // Edit menu
    async editMenu(id) {
        try {
            const response = await fetch(`api/manage_menu.php?id=${id}`);
            const result = await response.json();

            if (result.success) {
                const menu = result.data;

                document.getElementById('menuModalTitle').textContent = 'แก้ไขเมนู';
                document.getElementById('menuId').value = menu.id;
                document.getElementById('menuName').value = menu.name;
                document.getElementById('menuCategory').value = menu.category_id;
                document.getElementById('menuPrice').value = menu.price;
                document.getElementById('menuStatus').checked = menu.is_active == 1;

                const modal = new bootstrap.Modal(document.getElementById('menuModal'));
                modal.show();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            this.showNotification('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
        }
    }

    // Save menu (add or update)
    async saveMenu() {
        const id = document.getElementById('menuId').value;
        const name = document.getElementById('menuName').value.trim();
        const category_id = document.getElementById('menuCategory').value;
        const price = document.getElementById('menuPrice').value;
        const is_active = document.getElementById('menuStatus').checked ? 1 : 0;

        // Validate
        if (!name || !category_id || !price) {
            this.showNotification('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
            return;
        }

        const data = {
            name: name,
            category_id: parseInt(category_id),
            price: parseFloat(price),
            is_active: is_active
        };

        try {
            let url = 'api/manage_menu.php';
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
                this.showNotification(id ? 'แก้ไขเมนูสำเร็จ' : 'เพิ่มเมนูสำเร็จ', 'success');
                bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide();
                this.loadMenus();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error saving menu:', error);
            this.showNotification('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }
    }

    // Delete menu
    deleteMenu(id, name) {
        this.deleteId = id;
        document.getElementById('deleteMenuName').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Confirm delete
    async confirmDelete() {
        if (!this.deleteId) return;

        try {
            const response = await fetch(`api/manage_menu.php?id=${this.deleteId}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('ลบเมนูสำเร็จ', 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                this.deleteId = null;
                this.loadMenus();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error deleting menu:', error);
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
const menuManagement = new MenuManagement();

// Global functions for onclick events
function openMenuModal() {
    menuManagement.openMenuModal();
}

function saveMenu() {
    menuManagement.saveMenu();
}

function searchMenus() {
    menuManagement.searchMenus();
}

function filterMenus() {
    menuManagement.filterMenus();
}

function resetFilters() {
    menuManagement.resetFilters();
}

function confirmDelete() {
    menuManagement.confirmDelete();
}
