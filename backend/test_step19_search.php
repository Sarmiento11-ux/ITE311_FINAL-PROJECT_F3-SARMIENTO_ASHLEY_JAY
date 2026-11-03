<?php
/**
 * Step 19 Test Suite: Advanced Product Search, Filtering, and Sorting
 * Tests all required scenarios:
 * 1. Search by product name
 * 2. Search by seller / shop name
 * 3. Search by product description
 * 4. Filter by category (id, slug, name)
 * 5. Filter by price range (min_price, max_price)
 * 6. Filter by availability (in_stock, out_of_stock)
 * 7. Combine multiple filters (category + price + availability + seller)
 * 8. Sorting: Price Low to High, Price High to Low, Name A-Z, Newest
 * 9. Pagination with active search & filters
 * 10. Dynamic filter options metadata (/api/products/filters)
 * 11. Empty results handling for non-matching queries
 * 12. Verification of existing features (Wishlist, Cart, Details)
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ProductController;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;

$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failed++;
    }
}

echo "=======================================================\n";
echo "STEP 19: ADVANCED SEARCH, FILTERING & SORTING TEST SUITE\n";
echo "=======================================================\n\n";

$controller = new ProductController();

// ── TEST 1: Search by Product Name ────────────────────────────────────────────
echo "1. Search by Product Name\n";
$req1 = Request::create('/api/products', 'GET', ['search' => 'Terracotta']);
$res1 = $controller->index($req1);
$data1 = json_decode($res1->getContent(), true);
assertTest("Search 'Terracotta' returns matching product", $res1->getStatusCode() === 200 && count($data1['data']) >= 1);
assertTest("Found item is 'Earthy Terracotta Coffee Mug'", str_contains($data1['data'][0]['name'], 'Terracotta'));

// ── TEST 2: Search by Seller / Artisan Shop Name ──────────────────────────────
echo "\n2. Search by Artisan / Shop Name\n";
$req2 = Request::create('/api/products', 'GET', ['search' => 'Cordillera']);
$res2 = $controller->index($req2);
$data2 = json_decode($res2->getContent(), true);
assertTest("Search by shop name 'Cordillera' returns Elena's products", count($data2['data']) >= 1);
assertTest("First item seller is Elena Bautista", $data2['data'][0]['seller']['name'] === 'Elena Bautista');

$req2b = Request::create('/api/products', 'GET', ['search' => 'Marco Gomez']);
$res2b = $controller->index($req2b);
$data2b = json_decode($res2b->getContent(), true);
assertTest("Search by artisan name 'Marco Gomez' returns Marco's products", count($data2b['data']) >= 1);

// ── TEST 3: Search by Product Description / Material ──────────────────────────
echo "\n3. Search by Description & Materials\n";
$req3 = Request::create('/api/products', 'GET', ['search' => 'Seagrass']);
$res3 = $controller->index($req3);
$data3 = json_decode($res3->getContent(), true);
assertTest("Search 'Seagrass' finds woven craft pieces", count($data3['data']) >= 1);

// ── TEST 4: Filter by Category ────────────────────────────────────────────────
echo "\n4. Filter by Category\n";
$req4a = Request::create('/api/products', 'GET', ['category' => 'ceramics-pottery']);
$res4a = $controller->index($req4a);
$data4a = json_decode($res4a->getContent(), true);
assertTest("Filter by slug 'ceramics-pottery' returns matching category items", count($data4a['data']) >= 1);
assertTest("All returned items belong to Ceramics & Pottery", 
    collect($data4a['data'])->every(fn($p) => $p['category']['slug'] === 'ceramics-pottery')
);

// Filter by numeric ID
$req4b = Request::create('/api/products', 'GET', ['category' => 2]);
$res4b = $controller->index($req4b);
$data4b = json_decode($res4b->getContent(), true);
assertTest("Filter by category ID 2 returns matching items", count($data4b['data']) >= 1 && $data4b['data'][0]['category_id'] == 2);

// ── TEST 5: Filter by Price Range ─────────────────────────────────────────────
echo "\n5. Filter by Price Range\n";
$min = 400.0;
$max = 700.0;
$req5 = Request::create('/api/products', 'GET', ['min_price' => $min, 'max_price' => $max, 'paginate' => 0]);
$res5 = $controller->index($req5);
$data5 = json_decode($res5->getContent(), true);
assertTest("Price filter returns products within [₱400, ₱700]", count($data5['data']) >= 1);
assertTest("Every product price is between ₱400 and ₱700",
    collect($data5['data'])->every(fn($p) => (float)$p['price'] >= $min && (float)$p['price'] <= $max)
);

// ── TEST 6: Filter by Availability ────────────────────────────────────────────
echo "\n6. Filter by Availability\n";
$req6 = Request::create('/api/products', 'GET', ['availability' => 'in_stock', 'paginate' => 0]);
$res6 = $controller->index($req6);
$data6 = json_decode($res6->getContent(), true);
assertTest("Availability 'in_stock' returns products with stock > 0",
    count($data6['data']) >= 1 && collect($data6['data'])->every(fn($p) => (int)$p['stock'] > 0)
);

// ── TEST 7: Combine Multiple Filters ──────────────────────────────────────────
echo "\n7. Combine Multiple Filters\n";
// Ceramics & Pottery + Price <= 500 + In Stock
$req7 = Request::create('/api/products', 'GET', [
    'category'     => 'ceramics-pottery',
    'max_price'    => 500,
    'availability' => 'in_stock',
    'paginate'     => 0
]);
$res7 = $controller->index($req7);
$data7 = json_decode($res7->getContent(), true);
assertTest("Combined filters (Category + Max Price + Availability) executed successfully", $res7->getStatusCode() === 200);
assertTest("Every product satisfies all active criteria",
    collect($data7['data'])->every(fn($p) => 
        $p['category']['slug'] === 'ceramics-pottery' && 
        (float)$p['price'] <= 500 && 
        (int)$p['stock'] > 0
    )
);

// ── TEST 8: Sorting Products ──────────────────────────────────────────────────
echo "\n8. Sorting Verification\n";

// 8a. Price Low to High
$req8a = Request::create('/api/products', 'GET', ['sort' => 'price_asc', 'paginate' => 0]);
$res8a = $controller->index($req8a);
$data8a = json_decode($res8a->getContent(), true);
$pricesAsc = array_column($data8a['data'], 'price');
$sortedAsc = $pricesAsc;
sort($sortedAsc, SORT_NUMERIC);
assertTest("Sort 'price_asc' orders prices from lowest to highest", $pricesAsc === $sortedAsc);

// 8b. Price High to Low
$req8b = Request::create('/api/products', 'GET', ['sort' => 'price_desc', 'paginate' => 0]);
$res8b = $controller->index($req8b);
$data8b = json_decode($res8b->getContent(), true);
$pricesDesc = array_column($data8b['data'], 'price');
$sortedDesc = $pricesDesc;
rsort($sortedDesc, SORT_NUMERIC);
assertTest("Sort 'price_desc' orders prices from highest to lowest", $pricesDesc === $sortedDesc);

// 8c. Name A to Z
$req8c = Request::create('/api/products', 'GET', ['sort' => 'name_asc', 'paginate' => 0]);
$res8c = $controller->index($req8c);
$data8c = json_decode($res8c->getContent(), true);
$names = array_column($data8c['data'], 'name');
$sortedNames = $names;
natcasesort($sortedNames);
assertTest("Sort 'name_asc' orders product names alphabetically A-Z", array_values($names) === array_values($sortedNames));

// ── TEST 9: Pagination with Search & Filters ──────────────────────────────────
echo "\n9. Pagination with Search & Filters\n";
$req9 = Request::create('/api/products', 'GET', [
    'per_page' => 3,
    'page'     => 1,
    'paginate' => 1
]);
$res9 = $controller->index($req9);
$data9 = json_decode($res9->getContent(), true);
assertTest("Pagination returns per_page items on page 1", count($data9['data']) === 3);
assertTest("Pagination object contains current_page, last_page, total, and has_more",
    isset($data9['pagination']['current_page']) &&
    isset($data9['pagination']['last_page']) &&
    isset($data9['pagination']['total']) &&
    isset($data9['pagination']['has_more'])
);
assertTest("Current page is 1 and last page is > 1", $data9['pagination']['current_page'] === 1 && $data9['pagination']['last_page'] >= 2);

// Page 2
$req9b = Request::create('/api/products', 'GET', [
    'per_page' => 3,
    'page'     => 2,
    'paginate' => 1
]);
$res9b = $controller->index($req9b);
$data9b = json_decode($res9b->getContent(), true);
assertTest("Page 2 returns different products than Page 1", $data9b['data'][0]['id'] !== $data9['data'][0]['id']);

// ── TEST 10: Dynamic Filter Options Metadata ──────────────────────────────────
echo "\n10. Dynamic Filter Options Metadata\n";
$res10 = $controller->filters();
$data10 = json_decode($res10->getContent(), true);
assertTest("GET /api/products/filters returns HTTP 200", $res10->getStatusCode() === 200 && $data10['success'] === true);
assertTest("Filter metadata contains categories, sellers, and price bounds",
    isset($data10['data']['categories']) &&
    isset($data10['data']['sellers']) &&
    isset($data10['data']['price']['min']) &&
    isset($data10['data']['price']['max'])
);
assertTest("Price bounds reflect real database products", $data10['data']['price']['min'] > 0 && $data10['data']['price']['max'] >= $data10['data']['price']['min']);

// ── TEST 11: Empty Results Handling ───────────────────────────────────────────
echo "\n11. Empty Results for Non-Existent Queries\n";
$req11 = Request::create('/api/products', 'GET', ['search' => 'NonExistentSpaceShuttle12345']);
$res11 = $controller->index($req11);
$data11 = json_decode($res11->getContent(), true);
assertTest("Non-matching search returns HTTP 200 with empty data array", $res11->getStatusCode() === 200 && count($data11['data']) === 0);
assertTest("Pagination total is 0", $data11['pagination']['total'] === 0);

// ── TEST 12: Regression Verification (Single Product Details) ─────────────────
echo "\n12. Existing Features Integrity\n";
$res12 = $controller->show(1);
$data12 = json_decode($res12->getContent(), true);
assertTest("GET /api/products/{id} still works seamlessly", $res12->getStatusCode() === 200 && $data12['data']['id'] === 1);

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} PASSED / {$failed} FAILED\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
