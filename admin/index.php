<?php
require_once '../includes/config.php';

if (!Session::isAdmin()) {
    Utils::redirect('../index.html');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hero Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../index.html" class="logo">Hero Digital Admin</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="../index.html">View Store</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="logout()">Logout</button>
                </div>
            </div>
        </div>
    </header>

    <main class="products-section">
        <div class="container">
            <h1 class="section-title">Admin Dashboard</h1>
            <div id="admin-message" class="mb-2"></div>

            <div class="product-card">
                <div class="product-info">
                    <h2 class="product-title">Products</h2>
                    <div class="mb-2">Manage the products shown on the storefront.</div>
                    <div class="spinner" id="admin-loading" style="display:none;"></div>
                    <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
                        <thead>
                            <tr>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">ID</th>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">Name</th>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">SKU</th>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">Price</th>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">Status</th>
                                <th style="text-align:left; border-bottom:1px solid #ddd; padding:0.5rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="admin-products-body"></tbody>
                    </table>

                    <h2 class="product-title mt-3">Add New Product</h2>
                    <form id="admin-product-form" class="mt-1">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" id="admin-product-name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">SKU</label>
                            <input type="text" id="admin-product-sku" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Price (USD)</label>
                            <input type="number" step="0.01" id="admin-product-price" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Short Description</label>
                            <input type="text" id="admin-product-short" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea id="admin-product-desc" class="form-input form-textarea"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category ID</label>
                            <input type="number" id="admin-product-category" class="form-input" placeholder="e.g. 1 for Software">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select id="admin-product-status" class="form-input">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="admin-product-featured">
                                Featured on homepage
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Create Product</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="products-section">
        <div class="container text-center">
            <p class="mb-1">&copy; Hero Digital. Admin Dashboard.</p>
        </div>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        initAdminProducts();
    });

    function initAdminProducts() {
        var form = document.getElementById('admin-product-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                createAdminProduct();
            });
        }
        loadAdminProducts();
    }

    function loadAdminProducts() {
        var loading = document.getElementById('admin-loading');
        var body = document.getElementById('admin-products-body');
        var msg = document.getElementById('admin-message');
        if (!body) return;
        if (loading) loading.style.display = 'block';
        fetch('../api/products.php?action=list&limit=50&offset=0')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                if (!data.success) {
                    if (msg) msg.innerHTML = '<div class="alert alert-error">Failed to load products.</div>';
                    return;
                }
                if (!data.products || data.products.length === 0) {
                    body.innerHTML = '<tr><td colspan="6" style="padding:0.5rem;">No products found.</td></tr>';
                    return;
                }
                body.innerHTML = '';
                data.products.forEach(function (p) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' + p.id + '</td>' +
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' + p.name + '</td>' +
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' + p.sku + '</td>' +
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' + (p.price ? ('$' + parseFloat(p.price).toFixed(2)) : '') + '</td>' +
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' + p.status + '</td>' +
                        '<td style="padding:0.5rem; border-bottom:1px solid #f0f0f0;">' +
                            '<button class="btn btn-danger" type="button" onclick="deleteAdminProduct(' + p.id + ')">Delete</button>' +
                        '</td>';
                    body.appendChild(tr);
                });
            })
            .catch(function () {
                if (loading) loading.style.display = 'none';
                if (msg) msg.innerHTML = '<div class="alert alert-error">Error contacting API.</div>';
            });
    }

    function createAdminProduct() {
        var name = document.getElementById('admin-product-name').value;
        var sku = document.getElementById('admin-product-sku').value;
        var price = document.getElementById('admin-product-price').value;
        var shortDesc = document.getElementById('admin-product-short').value;
        var desc = document.getElementById('admin-product-desc').value;
        var categoryId = document.getElementById('admin-product-category').value;
        var status = document.getElementById('admin-product-status').value;
        var featured = document.getElementById('admin-product-featured').checked;

        var payload = {
            name: name,
            sku: sku,
            price: parseFloat(price),
            short_description: shortDesc,
            description: desc,
            category_id: categoryId ? parseInt(categoryId, 10) : null,
            status: status,
            is_featured: featured
        };

        fetch('../api/products.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showAlert('Product created successfully', 'success');
                    document.getElementById('admin-product-form').reset();
                    loadAdminProducts();
                } else {
                    showAlert(data.message || 'Failed to create product', 'error');
                }
            })
            .catch(function () {
                showAlert('Failed to create product', 'error');
            });
    }

    function deleteAdminProduct(id) {
        if (!confirm('Delete this product?')) return;
        fetch('../api/products.php?action=delete&id=' + encodeURIComponent(id), {
            method: 'DELETE'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    showAlert('Product deleted', 'success');
                    loadAdminProducts();
                } else {
                    showAlert(data.message || 'Failed to delete product', 'error');
                }
            })
            .catch(function () {
                showAlert('Failed to delete product', 'error');
            });
    }
    </script>
</body>
</html>
