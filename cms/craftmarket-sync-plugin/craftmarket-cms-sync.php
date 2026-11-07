<?php
/**
 * Plugin Name: CraftMarket CMS Bridge & Sync
 * Plugin URI: https://craftmarket.ph
 * Description: Connects WordPress CMS Admin Panel with CraftMarket Laravel REST API for unified management of Website Content, Announcements, Products, Users, and Sales Reports.
 * Version: 1.0.0
 * Author: CraftMarket Engineering Team
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CraftMarket_CMS_Bridge {

    private $api_base = 'http://127.0.0.1:8000/api';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menus']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
    }

    /**
     * Register CraftMarket menus in WordPress Admin Panel.
     */
    public function register_admin_menus() {
        // Main CraftMarket Menu
        add_menu_page(
            'CraftMarket Control',
            'CraftMarket CMS',
            'manage_options',
            'craftmarket-hub',
            [$this, 'render_hub_page'],
            'dashicons-store',
            25
        );

        // Submenu 1: Marketplace Products
        add_submenu_page(
            'craftmarket-hub',
            'Marketplace Products',
            'Products & Crafts',
            'manage_options',
            'craftmarket-products',
            [$this, 'render_products_page']
        );

        // Submenu 2: User Data
        add_submenu_page(
            'craftmarket-hub',
            'Marketplace Users',
            'Marketplace Users',
            'manage_options',
            'craftmarket-users',
            [$this, 'render_users_page']
        );

        // Submenu 3: Financial & Sales Reports
        add_submenu_page(
            'craftmarket-hub',
            'Sales & Reports',
            'Escrow Reports',
            'manage_options',
            'craftmarket-reports',
            [$this, 'render_reports_page']
        );
    }

    /**
     * Helper to fetch data from Laravel API with graceful timeout.
     */
    private function fetch_api($endpoint) {
        $response = wp_remote_get($this->api_base . $endpoint, [
            'timeout' => 3,
            'headers' => ['Accept' => 'application/json']
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['data']) ? $data['data'] : null;
    }

    /**
     * Render Hub Overview Page.
     */
    public function render_hub_page() {
        ?>
        <div class="wrap">
            <h1>CraftMarket Headless CMS Management Hub</h1>
            <p>Welcome to the central administrative bridge connecting WordPress CMS and Laravel Transactional Engine.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 24px;">
                <div style="background: #fff; padding: 20px; border-left: 4px solid #2F4533; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>📢 Website Announcements</h3>
                    <p>Manage promotional bars, seasonal notices, and free shipping banners.</p>
                    <a href="<?php echo admin_url('edit.php'); ?>" class="button button-primary">Edit Announcements (Posts)</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #C25E3E; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>🏺 Products & Crafts</h3>
                    <p>Review active artisan product listings and live catalog stock levels.</p>
                    <a href="<?php echo admin_url('admin.php?page=craftmarket-products'); ?>" class="button button-primary">View Products</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #3B82F6; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>👥 User Data Management</h3>
                    <p>Oversee registered customer accounts and verified artisan merchants.</p>
                    <a href="<?php echo admin_url('admin.php?page=craftmarket-users'); ?>" class="button button-primary">Manage Users</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #10B981; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>📈 Financial & Escrow Reports</h3>
                    <p>Track Gross Merchandise Value (GMV), artisan payouts, and commissions.</p>
                    <a href="<?php echo admin_url('admin.php?page=craftmarket-reports'); ?>" class="button button-primary">View Reports</a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Products View.
     */
    public function render_products_page() {
        $products = $this->fetch_api('/products');
        ?>
        <div class="wrap">
            <h1>CraftMarket Artisan Products (CMS Integration)</h1>
            <p>Live craft items synchronized from Laravel Relational MySQL database.</p>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price (₱)</th>
                        <th>Stock Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($p['id']); ?></strong></td>
                                <td><strong><?php echo esc_html($p['name']); ?></strong></td>
                                <td><?php echo esc_html($p['category']['name'] ?? 'General Craft'); ?></td>
                                <td>₱<?php echo number_format((float)$p['price'], 2); ?></td>
                                <td><?php echo esc_html($p['stock']); ?> units</td>
                                <td><span style="color: #2F4533; font-weight: bold;"><?php echo esc_html(strtoupper($p['status'])); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No products found or Laravel API is offline on port 8000.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Users View.
     */
    public function render_users_page() {
        $users = $this->fetch_api('/users');
        ?>
        <div class="wrap">
            <h1>CraftMarket User Data & Roles (CMS Integration)</h1>
            <p>Customer shoppers, artisan sellers, and administrators registered in CraftMarket.</p>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>System Role</th>
                        <th>Subscription Plan</th>
                        <th>Account Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($u['id']); ?></strong></td>
                                <td><strong><?php echo esc_html($u['name']); ?></strong></td>
                                <td><?php echo esc_html($u['email']); ?></td>
                                <td><code><?php echo esc_html(strtoupper($u['role'])); ?></code></td>
                                <td><?php echo esc_html($u['subscription_plan'] === 'pro' ? '⭐ Artisan Pro' : 'Basic Free'); ?></td>
                                <td><?php echo (!empty($u['is_active'])) ? '✅ Active' : '❌ Deactivated'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No user data found or Laravel API is offline on port 8000.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Reports View.
     */
    public function render_reports_page() {
        $reports = $this->fetch_api('/admin/reports');
        ?>
        <div class="wrap">
            <h1>CraftMarket Platform Sales & Escrow Reports</h1>
            <p>System-wide transactional statistics and commission revenue.</p>

            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 200px;">
                    <div style="font-size: 13px; color: #646970;">Total Realized Sales</div>
                    <div style="font-size: 24px; font-weight: bold; color: #1d2327;">₱<?php echo number_format((float)($reports['total_sales'] ?? 0), 2); ?></div>
                </div>
                <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 200px;">
                    <div style="font-size: 13px; color: #646970;">Total Completed Orders</div>
                    <div style="font-size: 24px; font-weight: bold; color: #1d2327;"><?php echo (int)($reports['total_orders'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add CraftMarket widget to WordPress Dashboard.
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'craftmarket_quick_stat_widget',
            'CraftMarket Platform Summary',
            function() {
                echo '<p><strong>Headless System Status:</strong> Active & Connected to Laravel API</p>';
                echo '<p><a href="' . admin_url('admin.php?page=craftmarket-hub') . '" class="button">Open CraftMarket Hub &rarr;</a></p>';
            }
        );
    }
}

new CraftMarket_CMS_Bridge();
