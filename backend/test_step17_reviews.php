<?php
/**
 * Step 17 Test Suite: Product Reviews and Ratings System
 * Verifies all 9 scenarios specified in Step 17 requirements.
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ProductReviewController;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
echo "STEP 17: PRODUCT REVIEWS AND RATINGS TEST SUITE\n";
echo "=======================================================\n\n";

$controller = new ProductReviewController();

// Clear any existing reviews to start fresh
ProductReview::truncate();

$customer1 = User::find(1); // Maria Clara - has delivered order for Product 2
$customer2 = User::find(5); // Juan Dela Cruz - has NO orders
$seller    = User::find(2); // Elena Bautista - seller
$admin     = User::find(4); // Admin CraftMarket - admin
$product2  = Product::find(2); // Handwoven Abaca Market Tote (seller: 2)
$product4  = Product::find(4); // Scented Soy Candle (Order 2 is pending for user 1)

// ── TEST 1: Submit a valid review ─────────────────────────────────────────────
echo "1. Valid Review Submission\n";
Auth::login($customer1);
$req = Request::create("/api/products/{$product2->id}/reviews", 'POST', [
    'rating'  => 5,
    'comment' => 'Exceptional abaca tote bag! Super durable and true Filipino craftsmanship.',
]);
$res = $controller->store($req, $product2->id);
$data = json_decode($res->getContent(), true);
assertTest("Customer 1 can review a product from a delivered order", $res->getStatusCode() === 201 && $data['success'] === true);
$createdReviewId = $data['data']['id'] ?? null;
assertTest("Review is persisted in MySQL product_reviews table", ProductReview::where('id', $createdReviewId)->exists());

// ── TEST 2: Submit an invalid rating ───────────────────────────────────────────
echo "\n2. Invalid Rating Validation (boundary check)\n";
try {
    $reqInvalid = Request::create("/api/products/{$product2->id}/reviews", 'POST', [
        'rating'  => 6, // Invalid > 5
        'comment' => 'Too high',
    ]);
    $resInvalid = $controller->store($reqInvalid, $product2->id);
    assertTest("Rating > 5 should fail validation", false);
} catch (ValidationException $e) {
    assertTest("Rating > 5 rejected with ValidationException", true);
}

try {
    $reqZero = Request::create("/api/products/{$product2->id}/reviews", 'POST', [
        'rating'  => 0, // Invalid < 1
        'comment' => 'Too low',
    ]);
    $resZero = $controller->store($reqZero, $product2->id);
    assertTest("Rating < 1 should fail validation", false);
} catch (ValidationException $e) {
    assertTest("Rating < 1 rejected with ValidationException", true);
}

// ── TEST 3: Submit a review without purchasing the product ─────────────────────
echo "\n3. Unpurchased Product Review Rejection\n";
Auth::login($customer2); // Juan has never purchased Product 2
$reqUnpurchased = Request::create("/api/products/{$product2->id}/reviews", 'POST', [
    'rating'  => 5,
    'comment' => 'Looks cool, but I never bought it!',
]);
$resUnpurchased = $controller->store($reqUnpurchased, $product2->id);
$dataUnpurchased = json_decode($resUnpurchased->getContent(), true);
assertTest("Customer without purchase cannot review (HTTP 403)", $resUnpurchased->getStatusCode() === 403 && $dataUnpurchased['success'] === false);

// ── TEST 4: Submit a review for an incomplete order ───────────────────────────
echo "\n4. Incomplete Order Review Rejection\n";
Auth::login($customer1); // Maria has Product 4, but Order 2 is pending
$reqPending = Request::create("/api/products/{$product4->id}/reviews", 'POST', [
    'rating'  => 4,
    'comment' => 'Waiting for it to arrive',
]);
$resPending = $controller->store($reqPending, $product4->id);
$dataPending = json_decode($resPending->getContent(), true);
assertTest("Customer cannot review pending/incomplete order (HTTP 403)", $resPending->getStatusCode() === 403 && $dataPending['success'] === false);

// ── TEST 5: Prevent duplicate reviews for the same product ─────────────────────
echo "\n5. Prevent Duplicate Reviews\n";
Auth::login($customer1);
$reqDup = Request::create("/api/products/{$product2->id}/reviews", 'POST', [
    'rating'  => 4,
    'comment' => 'Reviewing a second time!',
]);
$resDup = $controller->store($reqDup, $product2->id);
$dataDup = json_decode($resDup->getContent(), true);
assertTest("Duplicate review rejected (HTTP 422)", $resDup->getStatusCode() === 422 && str_contains($dataDup['message'], 'already reviewed'));

// ── TEST 6: Edit existing review ──────────────────────────────────────────────
echo "\n6. Customer Edits Own Review\n";
Auth::login($customer1);
$reqEdit = Request::create("/api/products/{$product2->id}/reviews/{$createdReviewId}", 'PUT', [
    'rating'  => 4,
    'comment' => 'Updated review: Still great quality after 2 weeks of daily use!',
]);
$resEdit = $controller->update($reqEdit, $product2->id, $createdReviewId);
$dataEdit = json_decode($resEdit->getContent(), true);
assertTest("Customer can update their own review", $resEdit->getStatusCode() === 200 && $dataEdit['data']['rating'] === 4);
assertTest("Database updated with edited rating and comment", ProductReview::find($createdReviewId)->rating === 4);

// ── TEST 7: Seller cannot edit or delete customer reviews ──────────────────────
echo "\n7. Seller Restricted from Editing / Deleting Reviews\n";
Auth::login($seller); // Elena Bautista (seller of Product 2)
$reqSellerEdit = Request::create("/api/products/{$product2->id}/reviews/{$createdReviewId}", 'PUT', [
    'rating'  => 5,
    'comment' => 'Seller trying to change customer review to 5 stars!',
]);
$resSellerEdit = $controller->update($reqSellerEdit, $product2->id, $createdReviewId);
assertTest("Seller cannot edit customer review (HTTP 403)", $resSellerEdit->getStatusCode() === 403);

$resSellerDel = $controller->destroy($product2->id, $createdReviewId);
assertTest("Seller cannot delete customer review (HTTP 403)", $resSellerDel->getStatusCode() === 403);

// ── TEST 8: Seller Can Read Own Product Reviews ────────────────────────────────
echo "\n8. Seller Views Reviews For Own Products\n";
Auth::login($seller);
$reqSellerList = Request::create("/api/seller/reviews", 'GET');
$resSellerList = $controller->sellerReviews($reqSellerList);
$dataSellerList = json_decode($resSellerList->getContent(), true);
assertTest("Seller can view reviews for their products", $resSellerList->getStatusCode() === 200 && count($dataSellerList['data']) >= 1);
assertTest("Seller review item contains product_name and customer comment", isset($dataSellerList['data'][0]['product_name']) && isset($dataSellerList['data'][0]['comment']));

// ── TEST 9: Public Endpoint & Average Rating Calculation ──────────────────────
echo "\n9. Public Reviews and Average Rating Display\n";
// Let customer 6 also leave a review if we deliver an order for them, or test Product avg_rating accessor
$productFresh = Product::find($product2->id);
assertTest("Product avg_rating matches single review rating (4.0)", $productFresh->avg_rating === 4.0);
assertTest("Product review_count is 1", $productFresh->review_count === 1);

$resPublic = $controller->index($product2->id);
$dataPublic = json_decode($resPublic->getContent(), true);
assertTest("Public GET /api/products/{id}/reviews returns avg_rating and reviews array", $dataPublic['avg_rating'] == 4.0 && count($dataPublic['data']) === 1);

// ── TEST 10: Admin Oversight and Moderation Delete ─────────────────────────────
echo "\n10. Admin Review Oversight and Moderation\n";
Auth::login($admin);
$reqAdminList = Request::create("/api/admin/reviews", 'GET');
$resAdminList = $controller->adminIndex($reqAdminList);
$dataAdminList = json_decode($resAdminList->getContent(), true);
assertTest("Admin can view all reviews in marketplace", $resAdminList->getStatusCode() === 200 && count($dataAdminList['data']) >= 1);

// Admin deletes the review
$resAdminDel = $controller->destroy(0, $createdReviewId);
$dataAdminDel = json_decode($resAdminDel->getContent(), true);
assertTest("Admin can remove review for moderation", $resAdminDel->getStatusCode() === 200 && $dataAdminDel['success'] === true);
assertTest("Review is removed from MySQL", !ProductReview::where('id', $createdReviewId)->exists());

$productAfterDel = Product::find($product2->id);
assertTest("Product review_count resets to 0 after deletion", $productAfterDel->review_count === 0);
assertTest("Product avg_rating resets to 0.0 after deletion", $productAfterDel->avg_rating === 0.0);

echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} PASSED / {$failed} FAILED\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
