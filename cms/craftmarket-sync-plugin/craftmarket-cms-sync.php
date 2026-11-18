<?php
/**
 * Plugin Name: CraftMarket CMS Bridge & Sync
 * Plugin URI: https://craftmarket.ph
 * Description: Connects the WordPress CMS Admin Panel with the CraftMarket Laravel REST API for unified management of Website Content, Announcements, Product Categories, Artisan Verification, Products, Users, and Sales Reports.
 * Version: 1.2.0
 * Author: CraftMarket Engineering Team
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CraftMarket_CMS_Bridge {

    /** @var string Laravel API base, e.g. http://127.0.0.1:8000/api */
    private $api_base;

    /** Product statuses accepted by PATCH /api/cms-bridge/products/{id}/status */
    private $product_statuses = ['active', 'inactive', 'out_of_stock', 'archived'];

    public function __construct() {
        $this->api_base = $this->resolve_api_base();

        add_action('admin_menu', [$this, 'register_admin_menus']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);

        // Write actions: nonce + capability are verified inside each handler.
        add_action('admin_post_craftmarket_product_status', [$this, 'handle_product_status']);
        add_action('admin_post_craftmarket_toggle_user', [$this, 'handle_toggle_user']);
        add_action('admin_post_craftmarket_category_create', [$this, 'handle_category_create']);
        add_action('admin_post_craftmarket_category_update', [$this, 'handle_category_update']);
        add_action('admin_post_craftmarket_category_delete', [$this, 'handle_category_delete']);
        add_action('admin_post_craftmarket_seller_verification', [$this, 'handle_seller_verification']);
    }

    /**
     * Allow the Laravel API location to be configured without editing code.
     * Define CRAFTMARKET_API_BASE in wp-config.php or use the filter below.
     */
    private function resolve_api_base() {
        if (defined('CRAFTMARKET_API_BASE')) {
            return untrailingslashit(CRAFTMARKET_API_BASE);
        }

        return untrailingslashit(apply_filters('craftmarket_api_base', 'http://127.0.0.1:8000/api'));
    }

    /**
     * Resolve the shared secret used to authenticate against Laravel's
     * /api/cms-bridge endpoints. Define CRAFTMARKET_CMS_BRIDGE_KEY in
     * wp-config.php and set the same value in the Laravel .env file.
     */
    private function bridge_key() {
        if (defined('CRAFTMARKET_CMS_BRIDGE_KEY')) {
            return CRAFTMARKET_CMS_BRIDGE_KEY;
        }

        return apply_filters('craftmarket_cms_bridge_key', 'dev-cms-bridge-key');
    }

    /**
     * Register CraftMarket menus in WordPress Admin Panel.
     */
    public function register_admin_menus() {
        add_menu_page(
            'CraftMarket Control',
            'CraftMarket CMS',
            'manage_options',
            'craftmarket-hub',
            [$this, 'render_hub_page'],
            'dashicons-store',
            25
        );

        add_submenu_page(
            'craftmarket-hub',
            'Marketplace Products',
            'Products & Crafts',
            'manage_options',
            'craftmarket-products',
            [$this, 'render_products_page']
        );

        add_submenu_page(
            'craftmarket-hub',
            'Category Taxonomy',
            'Categories',
            'manage_options',
            'craftmarket-categories',
            [$this, 'render_categories_page']
        );

        add_submenu_page(
            'craftmarket-hub',
            'Artisan Verification',
            'Artisan Verification',
            'manage_options',
            'craftmarket-verification',
            [$this, 'render_verification_page']
        );

        add_submenu_page(
            'craftmarket-hub',
            'Marketplace Users',
            'Marketplace Users',
            'manage_options',
            'craftmarket-users',
            [$this, 'render_users_page']
        );

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
     * Shared headers for every bridge request.
     */
    private function api_headers() {
        return [
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
            'X-CMS-Bridge-Key' => $this->bridge_key(),
        ];
    }

    /**
     * GET helper with graceful timeout. Returns the `data` payload or null.
     */
    private function fetch_api($endpoint, $query = []) {
        $url = $this->api_base . $endpoint;
        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }

        $timeout = defined('CRAFTMARKET_CMS_TIMEOUT') ? (int) CRAFTMARKET_CMS_TIMEOUT : (int) apply_filters('craftmarket_cms_timeout', 15);
        $response = wp_remote_get($url, [
            'timeout' => $timeout,
            'headers' => $this->api_headers(),
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        return isset($data['data']) ? $data['data'] : null;
    }

    /**
     * Write helper for PATCH/POST requests.
     *
     * @return array{ok: bool, message: string, data: mixed}
     */
    private function request_api($endpoint, array $payload, $method = 'POST') {
        $timeout = defined('CRAFTMARKET_CMS_TIMEOUT') ? (int) CRAFTMARKET_CMS_TIMEOUT : (int) apply_filters('craftmarket_cms_timeout', 15);
        $response = wp_remote_request($this->api_base . $endpoint, [
            'method'  => $method,
            'timeout' => $timeout,
            'headers' => $this->api_headers(),
            'body'    => wp_json_encode($payload),
        ]);

        if (is_wp_error($response)) {
            return [
                'ok'      => false,
                'message' => $response->get_error_message(),
                'data'    => null,
            ];
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = json_decode(wp_remote_retrieve_body($response), true);
        $ok     = $status >= 200 && $status < 300;

        return [
            'ok'      => $ok,
            'message' => isset($body['message'])
                ? (string) $body['message']
                : ($ok ? 'Request completed.' : "Laravel API returned HTTP {$status}."),
            'data'    => isset($body['data']) ? $body['data'] : null,
        ];
    }

    /**
     * Redirect back to a CraftMarket page with a status notice.
     */
    private function redirect_with_notice($page, $ok, $message) {
        $url = add_query_arg([
            'page'                 => $page,
            'craftmarket_notice'   => $ok ? 'success' : 'error',
            'craftmarket_message'  => rawurlencode($message),
        ], admin_url('admin.php'));

        wp_safe_redirect($url);
        exit;
    }

    /**
     * Render the result of a previous write action, if any.
     */
    private function render_notice() {
        if (empty($_GET['craftmarket_notice'])) {
            return;
        }

        $type    = $_GET['craftmarket_notice'] === 'success' ? 'success' : 'error';
        $message = isset($_GET['craftmarket_message'])
            ? sanitize_text_field(wp_unslash($_GET['craftmarket_message']))
            : '';

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }

    /**
     * Render Hub Overview Page.
     */
    public function render_hub_page() {
        $reports = $this->fetch_api('/cms-bridge/reports');
        ?>
        <div class="wrap">
            <h1>CraftMarket Headless CMS Management Hub</h1>
            <p>Central administrative bridge between the WordPress CMS and the Laravel transactional engine.</p>
            <?php $this->render_notice(); ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 24px;">
                <div style="background: #fff; padding: 20px; border-left: 4px solid #2F4533; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>Website Content & Announcements</h3>
                    <p>Manage promotional bars, seasonal notices, and the About page as WordPress pages and posts.</p>
                    <a href="<?php echo esc_url(admin_url('edit.php')); ?>" class="button button-primary">Edit Announcements (Posts)</a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>" class="button">Edit Pages</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #C25E3E; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>Products & Services</h3>
                    <p>Review artisan listings, then set each product to active, inactive, out of stock, or archived.</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-products')); ?>" class="button button-primary">Manage Products</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #8B5CF6; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>Category Taxonomy</h3>
                    <p>Add, rename, publish, or retire product categories. Deletion is blocked while a category still owns listings.</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-categories')); ?>" class="button button-primary">Manage Categories</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #D97706; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>Artisan Verification</h3>
                    <p>Approve or reject artisan storefront applications and send moderator notes to the maker by email.</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-verification')); ?>" class="button button-primary">Review Storefronts</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #3B82F6; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>User Data Management</h3>
                    <p>Oversee customers and artisans, and activate or deactivate marketplace accounts.</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-users')); ?>" class="button button-primary">Manage Users</a>
                </div>

                <div style="background: #fff; padding: 20px; border-left: 4px solid #10B981; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3>Reports</h3>
                    <p>Track GMV, commissions, escrow exposure, artisan payouts, and subscription revenue.</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-reports')); ?>" class="button button-primary">View Reports</a>
                </div>
            </div>

            <?php if (is_array($reports)): ?>
                <h2 style="margin-top: 32px;">Live Platform Snapshot</h2>
                <div style="display: flex; flex-wrap: wrap; gap: 16px;">
                    <?php
                    $cards = [
                        'Total Sales'      => '₱' . number_format((float) ($reports['total_sales'] ?? 0), 2),
                        'Orders'           => number_format((int) ($reports['total_orders'] ?? 0)),
                        'Commission'       => '₱' . number_format((float) ($reports['total_commission'] ?? 0), 2),
                        'Monthly Recurring'=> '₱' . number_format((float) ($reports['subscription_revenue']['mrr'] ?? 0), 2),
                    ];
                    foreach ($cards as $label => $value): ?>
                        <div style="background: #fff; padding: 16px 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 190px;">
                            <div style="font-size: 12px; color: #646970; text-transform: uppercase; letter-spacing: .04em;"><?php echo esc_html($label); ?></div>
                            <div style="font-size: 22px; font-weight: 700; color: #1d2327;"><?php echo esc_html($value); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="notice notice-warning" style="margin-top: 24px;">
                    <p>The Laravel API is unreachable at <code><?php echo esc_html($this->api_base); ?></code>. Start <code>php artisan serve</code> and confirm <code>CRAFTMARKET_API_BASE</code>.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Products View with moderation controls.
     */
    public function render_products_page() {
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : 'all';

        $query = ['status' => $status !== '' ? $status : 'all'];
        if ($search !== '') {
            $query['search'] = $search;
        }

        $products = $this->fetch_api('/cms-bridge/products', $query);
        ?>
        <div class="wrap">
            <h1>CraftMarket Artisan Products (CMS Integration)</h1>
            <p>Live craft listings synchronized from the Laravel relational database. Moderation is limited to listing status; pricing, stock, and ownership stay with the artisan and Laravel.</p>
            <?php $this->render_notice(); ?>

            <form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="page" value="craftmarket-products" />
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search product or artisan…" />
                <select name="status">
                    <?php foreach (array_merge(['all'], $this->product_statuses) as $option): ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($status, $option); ?>>
                            <?php echo esc_html($option === 'all' ? 'All statuses' : ucwords(str_replace('_', ' ', $option))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button('Filter', 'secondary', '', false); ?>
            </form>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Product Name</th>
                        <th>Artisan</th>
                        <th>Category</th>
                        <th>Price (₱)</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th style="width: 230px;">Moderate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($p['id']); ?></strong></td>
                                <td><strong><?php echo esc_html($p['name']); ?></strong></td>
                                <td>
                                    <?php echo esc_html($p['seller']['name'] ?? 'Unknown artisan'); ?><br />
                                    <small><?php echo esc_html($p['seller']['email'] ?? ''); ?></small>
                                </td>
                                <td><?php echo esc_html($p['category']['name'] ?? 'General Craft'); ?></td>
                                <td>₱<?php echo number_format((float) $p['price'], 2); ?></td>
                                <td><?php echo esc_html($p['stock']); ?> units</td>
                                <td><code><?php echo esc_html(strtoupper($p['status'])); ?></code></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: flex; gap: 6px;">
                                        <input type="hidden" name="action" value="craftmarket_product_status" />
                                        <input type="hidden" name="product_id" value="<?php echo esc_attr($p['id']); ?>" />
                                        <?php wp_nonce_field('craftmarket_product_status'); ?>
                                        <select name="status">
                                            <?php foreach ($this->product_statuses as $option): ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($p['status'], $option); ?>>
                                                    <?php echo esc_html(ucwords(str_replace('_', ' ', $option))); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php submit_button('Update', 'small', '', false); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No products found, or the Laravel API is offline at <code><?php echo esc_html($this->api_base); ?></code>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Category Taxonomy with add / edit / delete controls.
     */
    public function render_categories_page() {
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : 'all';

        $query = ['status' => in_array($status, ['active', 'inactive'], true) ? $status : 'all'];
        if ($search !== '') {
            $query['search'] = $search;
        }

        $categories = $this->fetch_api('/cms-bridge/categories', $query);
        $editing    = null;

        if (isset($_GET['edit']) && is_array($categories)) {
            $edit_id = absint($_GET['edit']);
            foreach ($categories as $candidate) {
                if ((int) $candidate['id'] === $edit_id) {
                    $editing = $candidate;
                    break;
                }
            }
        }

        $form = [
            'name'        => $editing['name'] ?? '',
            'slug'        => $editing['slug'] ?? '',
            'description' => $editing['description'] ?? '',
            'image'       => $editing['image'] ?? '',
            'is_active'   => isset($editing['is_active']) ? (bool) $editing['is_active'] : true,
        ];
        $form_action = $editing ? 'craftmarket_category_update' : 'craftmarket_category_create';
        ?>
        <div class="wrap">
            <h1>CraftMarket Category Taxonomy (CMS Integration)</h1>
            <p>Marketing-owned catalog taxonomy. WordPress is the authority for category add, edit, and delete; the Laravel storefront reflects changes immediately.</p>
            <?php $this->render_notice(); ?>

            <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0; max-width: 760px;">
                <h2 style="margin-top: 0;"><?php echo $editing ? 'Edit Category' : 'Add New Category'; ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>" />
                    <?php if ($editing): ?>
                        <input type="hidden" name="category_id" value="<?php echo esc_attr($editing['id']); ?>" />
                    <?php endif; ?>
                    <?php wp_nonce_field($form_action); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="craftmarket-category-name">Name <span style="color: #b32d2e;">*</span></label></th>
                            <td><input type="text" id="craftmarket-category-name" name="name" class="regular-text" required value="<?php echo esc_attr($form['name']); ?>" placeholder="e.g. Traditional Handweaves" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="craftmarket-category-slug">Slug</label></th>
                            <td>
                                <input type="text" id="craftmarket-category-slug" name="slug" class="regular-text" value="<?php echo esc_attr($form['slug']); ?>" placeholder="traditional-handweaves" />
                                <p class="description">Leave blank to generate the slug from the category name.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="craftmarket-category-description">Description</label></th>
                            <td><textarea id="craftmarket-category-description" name="description" rows="3" class="large-text"><?php echo esc_textarea($form['description']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="craftmarket-category-image">Image URL</label></th>
                            <td><input type="url" id="craftmarket-category-image" name="image" class="regular-text" value="<?php echo esc_attr($form['image']); ?>" placeholder="https://…" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="craftmarket-category-active">Status</label></th>
                            <td>
                                <select id="craftmarket-category-active" name="is_active">
                                    <option value="1" <?php selected($form['is_active'], true); ?>>Active (visible in catalog)</option>
                                    <option value="0" <?php selected($form['is_active'], false); ?>>Inactive (hidden)</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button($editing ? 'Save Changes' : 'Create Category', 'primary', 'submit', false); ?>
                    <?php if ($editing): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=craftmarket-categories')); ?>" class="button" style="margin-left: 8px;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="page" value="craftmarket-categories" />
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search category name or slug…" />
                <select name="status">
                    <option value="all" <?php selected($query['status'], 'all'); ?>>All statuses</option>
                    <option value="active" <?php selected($query['status'], 'active'); ?>>Active only</option>
                    <option value="inactive" <?php selected($query['status'], 'inactive'); ?>>Inactive only</option>
                </select>
                <?php submit_button('Filter', 'secondary', '', false); ?>
            </form>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th style="width: 110px;">Listings</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 190px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $c): ?>
                            <?php $product_count = (int) ($c['products_count'] ?? 0); ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($c['id']); ?></strong></td>
                                <td><strong><?php echo esc_html($c['name']); ?></strong></td>
                                <td><code><?php echo esc_html($c['slug']); ?></code></td>
                                <td><?php echo esc_html($c['description'] ?? '—'); ?></td>
                                <td><?php echo esc_html($product_count); ?> product(s)</td>
                                <td>
                                    <?php if (!empty($c['is_active'])): ?>
                                        <span style="color: #1a7f37;">Active</span>
                                    <?php else: ?>
                                        <span style="color: #b32d2e;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="button button-small" href="<?php echo esc_url(add_query_arg([
                                        'page' => 'craftmarket-categories',
                                        'edit' => (int) $c['id'],
                                    ], admin_url('admin.php'))); ?>">Edit</a>

                                    <?php if ($product_count > 0): ?>
                                        <span style="display: inline-block; margin-left: 6px; color: #646970;" title="Reassign or delete the linked products first.">Delete blocked</span>
                                    <?php else: ?>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline-block; margin-left: 6px;" onsubmit="return confirm('Delete category &quot;<?php echo esc_js($c['name']); ?>&quot;? This cannot be undone.');">
                                            <input type="hidden" name="action" value="craftmarket_category_delete" />
                                            <input type="hidden" name="category_id" value="<?php echo esc_attr($c['id']); ?>" />
                                            <?php wp_nonce_field('craftmarket_category_delete'); ?>
                                            <button type="submit" class="button button-small button-link-delete">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No categories found, or the Laravel API is offline at <code><?php echo esc_html($this->api_base); ?></code>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render artisan storefront verification queue.
     */
    public function render_verification_page() {
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $status = isset($_GET['verification_status']) ? sanitize_key(wp_unslash($_GET['verification_status'])) : '';

        $query = [];
        if ($search !== '') {
            $query['search'] = $search;
        }
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query['verification_status'] = $status;
        }

        $sellers = $this->fetch_api('/cms-bridge/sellers', $query);
        ?>
        <div class="wrap">
            <h1>CraftMarket Artisan Verification (CMS Integration)</h1>
            <p>WordPress is the single moderation authority for artisan storefronts. Approving or rejecting a workshop updates Laravel and emails the maker automatically.</p>
            <?php $this->render_notice(); ?>

            <form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="page" value="craftmarket-verification" />
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search artisan, email, or workshop…" />
                <select name="verification_status">
                    <option value="" <?php selected($status, ''); ?>>All verifications</option>
                    <option value="pending" <?php selected($status, 'pending'); ?>>Pending review</option>
                    <option value="approved" <?php selected($status, 'approved'); ?>>Approved</option>
                    <option value="rejected" <?php selected($status, 'rejected'); ?>>Rejected</option>
                </select>
                <?php submit_button('Filter', 'secondary', '', false); ?>
            </form>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Workshop</th>
                        <th>Artisan Maker</th>
                        <th>Contact</th>
                        <th style="width: 90px;">Listings</th>
                        <th style="width: 120px;">Verification</th>
                        <th style="width: 320px;">Moderation Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sellers)): ?>
                        <?php foreach ($sellers as $s): ?>
                            <?php
                            $profile       = $s['seller_profile'] ?? [];
                            $shop_name     = $s['shop_name'] ?? ($profile['shop_name'] ?? $s['name']);
                            $verification  = $s['verification_status'] ?? ($profile['verification_status'] ?? 'pending');
                            $notes         = $s['verification_notes'] ?? ($profile['verification_notes'] ?? '');
                            $badge_color   = $verification === 'approved' ? '#1a7f37' : ($verification === 'rejected' ? '#b32d2e' : '#996800');
                            ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($s['id']); ?></strong></td>
                                <td>
                                    <strong><?php echo esc_html($shop_name); ?></strong><br />
                                    <small><?php echo esc_html($profile['shop_description'] ?? ''); ?></small>
                                </td>
                                <td><?php echo esc_html($s['name']); ?></td>
                                <td>
                                    <?php echo esc_html($s['email']); ?><br />
                                    <small><?php echo esc_html($s['phone'] ?? 'No phone'); ?></small>
                                </td>
                                <td><?php echo esc_html((int) ($s['products_count'] ?? 0)); ?> craft(s)</td>
                                <td>
                                    <strong style="color: <?php echo esc_attr($badge_color); ?>;"><?php echo esc_html(strtoupper($verification)); ?></strong>
                                    <?php if (!empty($s['is_active'])): ?>
                                        <br /><small>Account active</small>
                                    <?php else: ?>
                                        <br /><small style="color: #b32d2e;">Account deactivated</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <input type="hidden" name="action" value="craftmarket_seller_verification" />
                                        <input type="hidden" name="seller_id" value="<?php echo esc_attr($s['id']); ?>" />
                                        <?php wp_nonce_field('craftmarket_seller_verification'); ?>
                                        <select name="status" style="margin-bottom: 6px;">
                                            <option value="pending" <?php selected($verification, 'pending'); ?>>Pending review</option>
                                            <option value="approved" <?php selected($verification, 'approved'); ?>>Approve storefront</option>
                                            <option value="rejected" <?php selected($verification, 'rejected'); ?>>Reject storefront</option>
                                        </select>
                                        <textarea name="notes" rows="2" class="large-text" placeholder="Moderator notes emailed to the artisan (optional)…"><?php echo esc_textarea($notes); ?></textarea>
                                        <?php submit_button('Save Decision', 'primary small', '', false); ?>
                                    </form>

                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 8px;">
                                        <input type="hidden" name="action" value="craftmarket_toggle_user" />
                                        <input type="hidden" name="user_id" value="<?php echo esc_attr($s['id']); ?>" />
                                        <input type="hidden" name="is_active" value="<?php echo !empty($s['is_active']) ? '0' : '1'; ?>" />
                                        <input type="hidden" name="redirect_to" value="craftmarket-sellers" />
                                        <?php wp_nonce_field('craftmarket_toggle_user'); ?>
                                        <?php submit_button(
                                            !empty($s['is_active']) ? 'Deactivate Account' : 'Activate Account',
                                            !empty($s['is_active']) ? 'delete small' : 'secondary small',
                                            '',
                                            false,
                                            ['onclick' => !empty($s['is_active']) ? "return confirm('Deactivating this artisan will revoke their active login sessions. Proceed?');" : '']
                                        ); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No artisan storefronts found, or the Laravel API is offline at <code><?php echo esc_html($this->api_base); ?></code>.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render Users View with activation controls.
     */
    public function render_users_page() {
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
        $role   = isset($_GET['role']) ? sanitize_key(wp_unslash($_GET['role'])) : '';

        $query = [];
        if ($search !== '') {
            $query['search'] = $search;
        }
        if (in_array($role, ['customer', 'seller', 'admin'], true)) {
            $query['role'] = $role;
        }

        $users = $this->fetch_api('/cms-bridge/users', $query);
        ?>
        <div class="wrap">
            <h1>CraftMarket User Data & Roles (CMS Integration)</h1>
            <p>Customer shoppers, artisan sellers, and administrators registered in CraftMarket. Deactivating an account immediately revokes its API sessions.</p>
            <?php $this->render_notice(); ?>

            <form method="get" style="margin: 16px 0; display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="page" value="craftmarket-users" />
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search name, email, phone…" />
                <select name="role">
                    <option value="">All roles</option>
                    <?php foreach (['customer', 'seller', 'admin'] as $option): ?>
                        <option value="<?php echo esc_attr($option); ?>" <?php selected($role, $option); ?>>
                            <?php echo esc_html(ucfirst($option)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button('Filter', 'secondary', '', false); ?>
            </form>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 18px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Role</th>
                        <th>Subscription</th>
                        <th>Storefront</th>
                        <th>Account</th>
                        <th style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <?php
                            $is_admin    = ($u['role'] ?? '') === 'admin';
                            $is_active   = !empty($u['is_active']);
                            $verification = $u['seller_profile']['verification_status'] ?? null;
                            ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($u['id']); ?></strong></td>
                                <td><strong><?php echo esc_html($u['name']); ?></strong></td>
                                <td><?php echo esc_html($u['email']); ?></td>
                                <td><code><?php echo esc_html(strtoupper($u['role'])); ?></code></td>
                                <td>
                                    <?php
                                    $plan = $u['subscription_plan'] ?? null;
                                    echo esc_html($plan === 'pro' ? 'Artisan Pro' : ($plan === 'basic' ? 'Basic' : 'Free'));
                                    ?>
                                </td>
                                <td><?php echo $verification ? esc_html(strtoupper($verification)) : '—'; ?></td>
                                <td><?php echo $is_active ? 'Active' : 'Deactivated'; ?></td>
                                <td>
                                    <?php if ($is_admin): ?>
                                        <span style="color: #646970;">Protected</span>
                                    <?php else: ?>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                            <input type="hidden" name="action" value="craftmarket_toggle_user" />
                                            <input type="hidden" name="user_id" value="<?php echo esc_attr($u['id']); ?>" />
                                            <input type="hidden" name="is_active" value="<?php echo $is_active ? '0' : '1'; ?>" />
                                            <?php wp_nonce_field('craftmarket_toggle_user'); ?>
                                            <?php
                                            submit_button(
                                                $is_active ? 'Deactivate' : 'Activate',
                                                $is_active ? 'delete small' : 'primary small',
                                                '',
                                                false
                                            );
                                            ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No user data found, or the Laravel API is offline at <code><?php echo esc_html($this->api_base); ?></code>.</td></tr>
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
        $reports = $this->fetch_api('/cms-bridge/reports');
        $payouts = is_array($reports) ? ($reports['payout_summary'] ?? []) : [];
        $subs    = is_array($reports) ? ($reports['subscription_revenue'] ?? []) : [];
        $ledger  = is_array($reports) ? ($reports['commission_ledger'] ?? []) : [];
        $orders  = is_array($reports) ? ($reports['order_status_summary'] ?? []) : [];
        $best    = is_array($reports) ? ($reports['best_selling_products'] ?? []) : [];
        ?>
        <div class="wrap">
            <h1>CraftMarket Platform Sales & Escrow Reports</h1>
            <p>System-wide transactional statistics, commission revenue, escrow exposure, and artisan payouts.</p>
            <?php $this->render_notice(); ?>

            <?php if (!is_array($reports)): ?>
                <div class="notice notice-error" style="margin-top: 20px;">
                    <p>Reports are unavailable. Confirm the Laravel API is running at <code><?php echo esc_html($this->api_base); ?></code>.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-wrap: wrap; gap: 16px; margin: 20px 0;">
                    <?php
                    $cards = [
                        'Gross Merchandise Value' => '₱' . number_format((float) ($reports['total_sales'] ?? 0), 2),
                        'Total Orders'            => number_format((int) ($reports['total_orders'] ?? 0)),
                        'Platform Commission'     => '₱' . number_format((float) ($reports['total_commission'] ?? 0), 2),
                        'Average Order Value'     => '₱' . number_format((float) ($reports['average_order_value'] ?? 0), 2),
                        'Monthly Recurring Rev.'  => '₱' . number_format((float) ($subs['mrr'] ?? 0), 2),
                        'Payouts Pending'         => '₱' . number_format((float) ($payouts['pending_amount'] ?? 0), 2),
                    ];
                    foreach ($cards as $label => $value): ?>
                        <div style="background: #fff; padding: 16px 20px; border: 1px solid #ccd0d4; border-radius: 4px; min-width: 200px;">
                            <div style="font-size: 12px; color: #646970; text-transform: uppercase; letter-spacing: .04em;"><?php echo esc_html($label); ?></div>
                            <div style="font-size: 22px; font-weight: 700; color: #1d2327;"><?php echo esc_html($value); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2>Escrow Commission Ledger</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Escrow Status</th>
                            <th>Orders</th>
                            <th>Platform Fees</th>
                            <th>Artisan Payouts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ledger as $escrowStatus => $row): ?>
                            <tr>
                                <td><code><?php echo esc_html(strtoupper(str_replace('_', ' ', $escrowStatus))); ?></code></td>
                                <td><?php echo (int) ($row['orders_count'] ?? 0); ?></td>
                                <td>₱<?php echo number_format((float) ($row['platform_fees'] ?? 0), 2); ?></td>
                                <td>₱<?php echo number_format((float) ($row['seller_payouts'] ?? 0), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h2 style="margin-top: 28px;">Artisan Payout Summary</h2>
                <table class="wp-list-table widefat fixed striped">
                    <tbody>
                        <?php
                        $payoutRows = [
                            'Pending Requests'  => [$payouts['pending_count'] ?? 0, $payouts['pending_amount'] ?? 0],
                            'Approved (Queued)' => [$payouts['approved_count'] ?? 0, $payouts['approved_amount'] ?? 0],
                            'Paid Out'          => [$payouts['paid_count'] ?? 0, $payouts['paid_amount'] ?? 0],
                            'Rejected'          => [$payouts['rejected_count'] ?? 0, $payouts['rejected_amount'] ?? 0],
                        ];
                        foreach ($payoutRows as $label => $pair): ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td><?php echo (int) $pair[0]; ?> request(s)</td>
                                <td>₱<?php echo number_format((float) $pair[1], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total Requested (All Time)</strong></td>
                            <td colspan="2">₱<?php echo number_format((float) ($payouts['total_requested'] ?? 0), 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <h2 style="margin-top: 28px;">Subscription Revenue (Artisan Monetization)</h2>
                <table class="wp-list-table widefat fixed striped">
                    <tbody>
                        <tr><td>Pro / Premium Subscribers</td><td><?php echo (int) ($subs['pro_subscribers_count'] ?? 0); ?></td></tr>
                        <tr><td>Basic Subscribers</td><td><?php echo (int) ($subs['basic_subscribers_count'] ?? 0); ?></td></tr>
                        <tr><td>Free Sellers</td><td><?php echo (int) ($subs['free_sellers_count'] ?? 0); ?></td></tr>
                        <tr><td><strong>Monthly Recurring Revenue</strong></td><td><strong>₱<?php echo number_format((float) ($subs['mrr'] ?? 0), 2); ?></strong></td></tr>
                        <tr><td>All-Time Subscription Revenue</td><td>₱<?php echo number_format((float) ($subs['all_time_revenue'] ?? 0), 2); ?></td></tr>
                    </tbody>
                </table>

                <h2 style="margin-top: 28px;">Order Status Breakdown</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead><tr><th>Status</th><th>Orders</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $status => $count): ?>
                            <tr>
                                <td><code><?php echo esc_html(strtoupper($status)); ?></code></td>
                                <td><?php echo (int) $count; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h2 style="margin-top: 28px;">Best-Selling Products</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead><tr><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php if (!empty($best)): ?>
                            <?php foreach ($best as $item): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($item['name'] ?? '—'); ?></strong></td>
                                    <td><?php echo esc_html($item['category'] ?? '—'); ?></td>
                                    <td><?php echo (int) ($item['total_sold'] ?? 0); ?></td>
                                    <td>₱<?php echo number_format((float) ($item['total_revenue'] ?? 0), 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No sales recorded for this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handle product status moderation.
     */
    public function handle_product_status() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to moderate CraftMarket products.', 403);
        }

        check_admin_referer('craftmarket_product_status');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $status     = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';

        if (!$product_id || !in_array($status, $this->product_statuses, true)) {
            $this->redirect_with_notice('craftmarket-products', false, 'Invalid product or status value.');
        }

        $result = $this->request_api(
            "/cms-bridge/products/{$product_id}/status",
            ['status' => $status],
            'PATCH'
        );

        $this->redirect_with_notice('craftmarket-products', $result['ok'], $result['message']);
    }

    /**
     * Handle account activation / deactivation.
     */
    public function handle_toggle_user() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage CraftMarket users.', 403);
        }

        check_admin_referer('craftmarket_toggle_user');

        $user_id  = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
        $isActive = isset($_POST['is_active']) ? (bool) absint($_POST['is_active']) : null;
        $redirectPage = isset($_POST['redirect_to']) && in_array($_POST['redirect_to'], ['craftmarket-sellers', 'craftmarket-users'], true)
            ? $_POST['redirect_to']
            : 'craftmarket-users';

        if (!$user_id || $isActive === null) {
            $this->redirect_with_notice($redirectPage, false, 'Invalid user or activation value.');
        }

        $result = $this->request_api(
            "/cms-bridge/users/{$user_id}/toggle-active",
            ['is_active' => $isActive],
            'PATCH'
        );

        $this->redirect_with_notice($redirectPage, $result['ok'], $result['message']);
    }

    /**
     * Collect and sanitize category form fields.
     */
    private function category_payload_from_request() {
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';

        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        return [
            'name'        => $name,
            'slug'        => $slug,
            'description' => isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '',
            'image'       => isset($_POST['image']) ? esc_url_raw(wp_unslash($_POST['image'])) : '',
            'is_active'   => !empty($_POST['is_active']),
        ];
    }

    /**
     * Create a category from the CMS.
     */
    public function handle_category_create() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage CraftMarket categories.', 403);
        }

        check_admin_referer('craftmarket_category_create');

        $payload = $this->category_payload_from_request();

        if ($payload['name'] === '' || $payload['slug'] === '') {
            $this->redirect_with_notice('craftmarket-categories', false, 'A category name is required.');
        }

        $result = $this->request_api('/cms-bridge/categories', $payload, 'POST');

        $this->redirect_with_notice('craftmarket-categories', $result['ok'], $result['message']);
    }

    /**
     * Update an existing category from the CMS.
     */
    public function handle_category_update() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage CraftMarket categories.', 403);
        }

        check_admin_referer('craftmarket_category_update');

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        $payload     = $this->category_payload_from_request();

        if (!$category_id || $payload['name'] === '' || $payload['slug'] === '') {
            $this->redirect_with_notice('craftmarket-categories', false, 'Invalid category or missing name.');
        }

        $result = $this->request_api("/cms-bridge/categories/{$category_id}", $payload, 'PUT');

        $this->redirect_with_notice('craftmarket-categories', $result['ok'], $result['message']);
    }

    /**
     * Delete a category. Laravel blocks deletion while products still reference it.
     */
    public function handle_category_delete() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to manage CraftMarket categories.', 403);
        }

        check_admin_referer('craftmarket_category_delete');

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;

        if (!$category_id) {
            $this->redirect_with_notice('craftmarket-categories', false, 'Invalid category selected.');
        }

        $result = $this->request_api("/cms-bridge/categories/{$category_id}", [], 'DELETE');

        $this->redirect_with_notice('craftmarket-categories', $result['ok'], $result['message']);
    }

    /**
     * Approve, reject, or return an artisan storefront to review.
     */
    public function handle_seller_verification() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to review CraftMarket storefronts.', 403);
        }

        check_admin_referer('craftmarket_seller_verification');

        $seller_id = isset($_POST['seller_id']) ? absint($_POST['seller_id']) : 0;
        $status    = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : '';
        $notes     = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';

        if (!$seller_id || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $this->redirect_with_notice('craftmarket-verification', false, 'Invalid artisan or verification status.');
        }

        $payload = ['status' => $status];
        if ($notes !== '') {
            $payload['notes'] = $notes;
        }

        $result = $this->request_api(
            "/cms-bridge/sellers/{$seller_id}/verification",
            $payload,
            'PATCH'
        );

        $this->redirect_with_notice('craftmarket-verification', $result['ok'], $result['message']);
    }

    /**
     * Add CraftMarket widget to WordPress Dashboard.
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'craftmarket_quick_stat_widget',
            'CraftMarket Platform Summary',
            function () {
                $reports = $this->fetch_api('/cms-bridge/reports');

                if (!is_array($reports)) {
                    echo '<p><strong>Headless System Status:</strong> Laravel API unreachable.</p>';
                } else {
                    echo '<p><strong>Headless System Status:</strong> Connected to Laravel API</p>';
                    echo '<p>Total sales: <strong>₱' . esc_html(number_format((float) ($reports['total_sales'] ?? 0), 2)) . '</strong></p>';
                    echo '<p>Platform commission: <strong>₱' . esc_html(number_format((float) ($reports['total_commission'] ?? 0), 2)) . '</strong></p>';
                }

                echo '<p><a href="' . esc_url(admin_url('admin.php?page=craftmarket-hub')) . '" class="button">Open CraftMarket Hub &rarr;</a></p>';
            }
        );
    }
}

new CraftMarket_CMS_Bridge();
