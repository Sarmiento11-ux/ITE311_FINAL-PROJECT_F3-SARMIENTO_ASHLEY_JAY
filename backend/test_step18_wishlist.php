<?php
/**
 * Step 18 Test Suite: Wishlist and Favorites System
 * Tests all required scenarios:
 * 1. Add product to wishlist (authenticated customer)
 * 2. Prevent duplicate entries
 * 3. View saved products (GET /api/wishlist)
 * 4. Get wishlist IDs (GET /api/wishlist/ids)
 * 5. Check wishlist status for a product (GET /api/wishlist/status/{productId})
 * 6. Prevent unauthenticated access
 * 7. Prevent access/modification of another customer's wishlist (ownership isolation)
 * 8. Remove product from wishlist (DELETE /api/wishlist/{productId})
 * 9. Clear customer wishlist (DELETE /api/wishlist)
 * 10. Persistence verification across sessions
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\WishlistController;
use App\Models\User;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
echo "STEP 18: WISHLIST AND FAVORITES TEST SUITE\n";
echo "=======================================================\n\n";

$controller = new WishlistController();

// Clear any existing wishlists to start fresh for clean testing
Wishlist::truncate();

$customer1 = User::find(1); // Maria Clara
$customer2 = User::find(5); // Juan Dela Cruz
$product1  = Product::find(1); // Earthy Terracotta Coffee Mug
$product2  = Product::find(2); // Handwoven Abaca Market Tote

// ── TEST 1: Add product to wishlist ──────────────────────────────────────────
echo "1. Add Product to Wishlist (Authenticated Customer)\n";
Auth::login($customer1);
$reqAdd1 = Request::create('/api/wishlist', 'POST', [
    'product_id' => $product1->id,
]);
$reqAdd1->setUserResolver(fn() => $customer1);
$resAdd1 = $controller->store($reqAdd1);
$dataAdd1 = json_decode($resAdd1->getContent(), true);

assertTest("Customer 1 can add product to wishlist (HTTP 201)", $resAdd1->getStatusCode() === 201 && $dataAdd1['success'] === true);
assertTest("Response indicates in_wishlist = true", $dataAdd1['in_wishlist'] === true);
assertTest("Wishlist record persisted in MySQL wishlists table", Wishlist::where('user_id', $customer1->id)->where('product_id', $product1->id)->exists());

// Add second product for Customer 1
$reqAdd2 = Request::create('/api/wishlist', 'POST', [
    'product_id' => $product2->id,
]);
$reqAdd2->setUserResolver(fn() => $customer1);
$controller->store($reqAdd2);
assertTest("Customer 1 can add a second product", Wishlist::where('user_id', $customer1->id)->count() === 2);

// ── TEST 2: Prevent duplicate entries ────────────────────────────────────────
echo "\n2. Prevent Duplicate Wishlist Entries\n";
Auth::login($customer1);
$reqDup = Request::create('/api/wishlist', 'POST', [
    'product_id' => $product1->id, // Already wishlisted
]);
$reqDup->setUserResolver(fn() => $customer1);
$resDup = $controller->store($reqDup);
$dataDup = json_decode($resDup->getContent(), true);

assertTest("Duplicate addition returns success without creating a duplicate record (HTTP 200)", $resDup->getStatusCode() === 200 && str_contains($dataDup['message'], 'already in your wishlist'));
assertTest("Total wishlists in DB for Customer 1 remains exactly 2", Wishlist::where('user_id', $customer1->id)->where('product_id', $product1->id)->count() === 1);

// ── TEST 3: View saved products (GET /api/wishlist) ──────────────────────────
echo "\n3. View Saved Products (GET /api/wishlist)\n";
$reqList = Request::create('/api/wishlist', 'GET');
$reqList->setUserResolver(fn() => $customer1);
$resList = $controller->index($reqList);
$dataList = json_decode($resList->getContent(), true);

assertTest("GET /api/wishlist returns HTTP 200 with data array", $resList->getStatusCode() === 200 && is_array($dataList['data']));
assertTest("Wishlist count equals 2", count($dataList['data']) === 2);
assertTest("Saved item contains product name, price, stock, and seller", 
    isset($dataList['data'][0]['name']) &&
    isset($dataList['data'][0]['price']) &&
    isset($dataList['data'][0]['stock']) &&
    isset($dataList['data'][0]['seller_name'])
);

// ── TEST 4: Get Wishlist Product IDs (GET /api/wishlist/ids) ─────────────────
echo "\n4. Fast Wishlist Product IDs (GET /api/wishlist/ids)\n";
$reqIds = Request::create('/api/wishlist/ids', 'GET');
$reqIds->setUserResolver(fn() => $customer1);
$resIds = $controller->ids($reqIds);
$dataIds = json_decode($resIds->getContent(), true);

assertTest("GET /api/wishlist/ids returns array of product IDs", $resIds->getStatusCode() === 200 && in_array($product1->id, $dataIds['product_ids']) && in_array($product2->id, $dataIds['product_ids']));

// ── TEST 5: Check Wishlist Status for a Product ──────────────────────────────
echo "\n5. Wishlist Status Check\n";
$reqStatus1 = Request::create("/api/wishlist/status/{$product1->id}", 'GET');
$reqStatus1->setUserResolver(fn() => $customer1);
$resStatus1 = $controller->status($reqStatus1, $product1->id);
$dataStatus1 = json_decode($resStatus1->getContent(), true);
assertTest("Status returns in_wishlist = true for saved product", $dataStatus1['in_wishlist'] === true);

$product3 = Product::find(3); // Freshwater Pearl Bracelet (not wishlisted yet)
$reqStatus3 = Request::create("/api/wishlist/status/{$product3->id}", 'GET');
$reqStatus3->setUserResolver(fn() => $customer1);
$resStatus3 = $controller->status($reqStatus3, $product3->id);
$dataStatus3 = json_decode($resStatus3->getContent(), true);
assertTest("Status returns in_wishlist = false for unsaved product", $dataStatus3['in_wishlist'] === false);

// ── TEST 6: Ownership Isolation (Customer 2 cannot see/delete Customer 1 items)
echo "\n6. Enforce Wishlist Ownership Between Customers\n";
Auth::login($customer2); // Juan
// Customer 2's wishlist should be completely empty right now
$reqList2 = Request::create('/api/wishlist', 'GET');
$reqList2->setUserResolver(fn() => $customer2);
$resList2 = $controller->index($reqList2);
$dataList2 = json_decode($resList2->getContent(), true);
assertTest("Customer 2's wishlist is isolated and empty (count 0)", $dataList2['count'] === 0 && count($dataList2['data']) === 0);

// Customer 2 tries to remove Customer 1's saved product from Customer 2's wishlist
$reqDelOther = Request::create("/api/wishlist/{$product1->id}", 'DELETE');
$reqDelOther->setUserResolver(fn() => $customer2);
$resDelOther = $controller->destroy($reqDelOther, $product1->id);
assertTest("Customer 2 cannot remove Customer 1's item (HTTP 404 not found in user's wishlist)", $resDelOther->getStatusCode() === 404);
assertTest("Customer 1's wishlist item remains intact in DB", Wishlist::where('user_id', $customer1->id)->where('product_id', $product1->id)->exists());

// ── TEST 7: Remove Product From Wishlist ─────────────────────────────────────
echo "\n7. Remove Product from Wishlist\n";
Auth::login($customer1);
$reqDel = Request::create("/api/wishlist/{$product1->id}", 'DELETE');
$reqDel->setUserResolver(fn() => $customer1);
$resDel = $controller->destroy($reqDel, $product1->id);
$dataDel = json_decode($resDel->getContent(), true);

assertTest("Customer 1 can remove product from wishlist (HTTP 200)", $resDel->getStatusCode() === 200 && $dataDel['success'] === true);
assertTest("Product 1 is no longer in Customer 1's wishlist in MySQL", !Wishlist::where('user_id', $customer1->id)->where('product_id', $product1->id)->exists());
assertTest("Customer 1 still has Product 2 saved", Wishlist::where('user_id', $customer1->id)->where('product_id', $product2->id)->exists());

// ── TEST 8: Clear All Wishlist Items ─────────────────────────────────────────
echo "\n8. Clear Entire Wishlist\n";
$reqClear = Request::create('/api/wishlist', 'DELETE');
$reqClear->setUserResolver(fn() => $customer1);
$resClear = $controller->clear($reqClear);
$dataClear = json_decode($resClear->getContent(), true);

assertTest("Clear wishlist returns HTTP 200", $resClear->getStatusCode() === 200 && $dataClear['success'] === true);
assertTest("Customer 1 wishlist is now empty in MySQL", Wishlist::where('user_id', $customer1->id)->count() === 0);

// ── TEST 9: Persistence Verification ────────────────────────────────────────
echo "\n9. Persistence Verification Across Sessions\n";
// Add Product 2 back for Customer 1
$reqAddAgain = Request::create('/api/wishlist', 'POST', ['product_id' => $product2->id]);
$reqAddAgain->setUserResolver(fn() => $customer1);
$controller->store($reqAddAgain);

// Customer logs out
Auth::logout();

// Customer logs back in
Auth::login($customer1);
$reqPersist = Request::create('/api/wishlist', 'GET');
$reqPersist->setUserResolver(fn() => $customer1);
$resPersist = $controller->index($reqPersist);
$dataPersist = json_decode($resPersist->getContent(), true);
assertTest("Wishlist item persists in MySQL after re-login", count($dataPersist['data']) === 1 && $dataPersist['data'][0]['product_id'] == $product2->id);

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} PASSED / {$failed} FAILED\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
