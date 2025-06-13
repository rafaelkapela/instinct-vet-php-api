<?php

require_once __DIR__ . '/../vendor/autoload.php';
use TynaTech\InstinctClass;

echo "=== Instinct API - Complete GET Methods Test ===\n\n";

$instinct = new InstinctClass();
echo "API URL: " . $instinct->getApiUrl() . "\n\n";

// List of all GET methods to test
$getMethods = [
    // Core methods
    ['method' => 'getVisits', 'params' => ['2025-06-12'], 'description' => 'Get visits for date'],
    ['method' => 'getAccounts', 'params' => [['limit' => 5]], 'description' => 'List accounts'],
    ['method' => 'getAppointmentTypes', 'params' => [['limit' => 5]], 'description' => 'List appointment types'],
    ['method' => 'getAlerts', 'params' => [['limit' => 5]], 'description' => 'List alerts'],
    
    // New methods
    ['method' => 'getAppointments', 'params' => [['limit' => 5]], 'description' => 'List appointments'],
    ['method' => 'getBreeds', 'params' => [['limit' => 10]], 'description' => 'List breeds'],
    ['method' => 'getDispensedPrescriptions', 'params' => [['limit' => 5]], 'description' => 'List dispensed prescriptions'],
    ['method' => 'getExternalPrescriptions', 'params' => [['limit' => 5]], 'description' => 'List external prescriptions'],
    ['method' => 'getInvoices', 'params' => [['limit' => 5]], 'description' => 'List invoices'],
    ['method' => 'getInvoiceLedgerEntries', 'params' => [['limit' => 5]], 'description' => 'List invoice ledger entries'],
    // getInvoiceLineItems will be tested separately with real invoice ID
    ['method' => 'getLocations', 'params' => [['limit' => 5]], 'description' => 'List locations'],
    ['method' => 'getPatients', 'params' => [['limit' => 10]], 'description' => 'List patients'],
    ['method' => 'getPaymentTransactions', 'params' => [['limit' => 5]], 'description' => 'List payment transactions'],
    ['method' => 'getProducts', 'params' => [['limit' => 10]], 'description' => 'List products'],
    ['method' => 'getReminderLabels', 'params' => [['limit' => 5]], 'description' => 'List reminder labels'],
    ['method' => 'getReminders', 'params' => [['limit' => 5]], 'description' => 'List reminders'],
    ['method' => 'getServices', 'params' => [['limit' => 10]], 'description' => 'List services'],
    ['method' => 'getTitles', 'params' => [['limit' => 5]], 'description' => 'List titles'],
    ['method' => 'getUsers', 'params' => [['limit' => 5]], 'description' => 'List users'],
];

$successCount = 0;
$totalCount = count($getMethods);
$firstIds = []; // Store first IDs for individual fetch tests

echo "Testing " . $totalCount . " GET methods...\n\n";

// Test all list methods
foreach ($getMethods as $index => $methodTest) {
    $methodName = $methodTest['method'];
    $params = $methodTest['params'];
    $description = $methodTest['description'];
    
    echo ($index + 1) . ". Testing {$methodName}() - {$description}...\n";
    
    try {
        $response = call_user_func_array([$instinct, $methodName], $params);
        
        if ($response && $response['success']) {
            echo "   ✅ SUCCESS - HTTP {$response['http_code']}\n";
            
            $dataCount = is_array($response['data']['data'] ?? null) ? count($response['data']['data']) : 0;
            echo "   📊 Returned: {$dataCount} items\n";
            
            // Store first ID for individual fetch tests
            if ($dataCount > 0 && isset($response['data']['data'][0]['id'])) {
                $firstId = $response['data']['data'][0]['id'];
                
                // Special handling for titles - skip problematic first ID
                if ($methodName === 'getTitles') {
                    // Find a valid title ID (skip first one if it's problematic)
                    foreach ($response['data']['data'] as $item) {
                        if (strlen(trim($item['id'])) > 0 && strlen($item['id']) <= 4 && ctype_alpha($item['id'])) {
                            $firstId = $item['id'];
                            break;
                        }
                    }
                }
                
                $firstIds[$methodName] = $firstId;
                echo "   🔑 First ID: {$firstId}\n";
            }
            
            // Show pagination info if available
            if (isset($response['data']['pageCursor'])) {
                echo "   📄 Page cursor available\n";
            }
            
            $successCount++;
        } else {
            echo "   ❌ FAILED - HTTP " . ($response['http_code'] ?? 'unknown') . "\n";
            if (isset($response['data']['message'])) {
                echo "   💬 Message: " . $response['data']['message'] . "\n";
            }
        }
    } catch (Exception $e) {
        echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test Invoice Line Items with real invoice ID
if (isset($firstIds['getInvoices'])) {
    $invoiceId = $firstIds['getInvoices'];
    echo "Testing getInvoiceLineItems('{$invoiceId}') - Invoice line items for specific invoice...\n";
    
    try {
        $response = $instinct->getInvoiceLineItems($invoiceId, ['limit' => 5]);
        
        if ($response && $response['success']) {
            echo "   ✅ SUCCESS - HTTP {$response['http_code']}\n";
            $dataCount = is_array($response['data']['data'] ?? null) ? count($response['data']['data']) : 0;
            echo "   📊 Returned: {$dataCount} line items\n";
            $successCount++;
        } else {
            echo "   ❌ FAILED - HTTP " . ($response['http_code'] ?? 'unknown') . "\n";
            if (isset($response['data']['message'])) {
                echo "   💬 Message: " . $response['data']['message'] . "\n";
            }
        }
    } catch (Exception $e) {
        echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    $totalCount++; // Increment total count for this extra test
}

echo "=== Individual Item Fetch Tests ===\n\n";

// Test individual fetch methods with collected IDs
$individualTests = [
    'getAccounts' => 'getAccount',
    'getAppointmentTypes' => 'getAppointmentType',
    'getAlerts' => 'getAlert',
    'getAppointments' => 'getAppointment',
    'getInvoices' => 'getInvoice',
    'getInvoiceLineItems' => 'getInvoiceLineItem',
    'getLocations' => 'getLocation',
    'getPatients' => 'getPatient',
    'getProducts' => 'getProduct',
    'getReminderLabels' => 'getReminderLabel',
    'getReminders' => 'getReminder',
    'getServices' => 'getService',
    'getTitles' => 'getTitle',
    'getUsers' => 'getUser',
];

$individualSuccessCount = 0;
$individualTestCount = 0;

foreach ($individualTests as $listMethod => $fetchMethod) {
    if (isset($firstIds[$listMethod])) {
        $individualTestCount++;
        $testId = $firstIds[$listMethod];
        
        echo "{$individualTestCount}. Testing {$fetchMethod}('{$testId}')...\n";
        
        try {
            $response = $instinct->$fetchMethod($testId);
            
            if ($response && $response['success']) {
                echo "   ✅ SUCCESS - HTTP {$response['http_code']}\n";
                echo "   📝 Item ID: " . ($response['data']['id'] ?? 'N/A') . "\n";
                
                // Show a few key fields if available
                $keyFields = ['name', 'title', 'type', 'status', 'description'];
                foreach ($keyFields as $field) {
                    if (isset($response['data'][$field])) {
                        echo "   📋 {$field}: " . $response['data'][$field] . "\n";
                        break; // Show only first available field
                    }
                }
                
                $individualSuccessCount++;
            } else {
                echo "   ❌ FAILED - HTTP " . ($response['http_code'] ?? 'unknown') . "\n";
                if (isset($response['data']['message'])) {
                    echo "   💬 Message: " . $response['data']['message'] . "\n";
                }
            }
        } catch (Exception $e) {
            echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
}

// Test special methods that require IDs but we might not have
echo "=== Special ID-based Methods Tests ===\n\n";

$specialTests = [
    ['method' => 'getOrder', 'testId' => 'ord_invalid_test_123', 'description' => 'Get order by ID'],
    ['method' => 'getTreatment', 'testId' => 'trt_invalid_test_123', 'description' => 'Get treatment by ID'],
    ['method' => 'getPdfRequestStatus', 'testId' => 'pdf_invalid_test_123', 'description' => 'Get PDF request status'],
];

// If we have patient or visit IDs, test the related methods
if (isset($firstIds['getPatients'])) {
    $specialTests[] = ['method' => 'getOrdersByVisit', 'testId' => 'visit_invalid_test_123', 'description' => 'Get orders by visit'];
}

$specialSuccessCount = 0;
$specialTestCount = count($specialTests);

foreach ($specialTests as $index => $test) {
    echo ($index + 1) . ". Testing {$test['method']}('{$test['testId']}') - {$test['description']}...\n";
    
    try {
        if ($test['method'] === 'getOrdersByVisit') {
            $response = $instinct->getOrdersByVisit($test['testId']);
        } else {
            $response = call_user_func([$instinct, $test['method']], $test['testId']);
        }
        
        if ($response) {
            echo "   ✅ REQUEST SENT - HTTP {$response['http_code']}\n";
            if ($response['success']) {
                echo "   🎯 Unexpected success with test ID\n";
                $specialSuccessCount++;
            } else {
                echo "   ⚠️  Expected failure with invalid test ID\n";
            }
        } else {
            echo "   ❌ REQUEST FAILED\n";
        }
    } catch (Exception $e) {
        echo "   ❌ EXCEPTION: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Summary
echo "=== TEST SUMMARY ===\n\n";
echo "List Methods: {$successCount}/{$totalCount} successful\n";
echo "Individual Fetch Methods: {$individualSuccessCount}/{$individualTestCount} successful\n";
echo "Special Methods: {$specialSuccessCount}/{$specialTestCount} had unexpected success\n";

$overallSuccess = $successCount + $individualSuccessCount;
$overallTotal = $totalCount + $individualTestCount;
$successRate = $overallTotal > 0 ? round(($overallSuccess / $overallTotal) * 100, 1) : 0;

echo "\nOverall Success Rate: {$successRate}% ({$overallSuccess}/{$overallTotal})\n";

if ($successRate >= 80) {
    echo "🎉 EXCELLENT! Most API methods are working correctly.\n";
} elseif ($successRate >= 60) {
    echo "👍 GOOD! Majority of API methods are functional.\n";
} elseif ($successRate >= 40) {
    echo "⚠️  PARTIAL! Some API methods need attention.\n";
} else {
    echo "❌ ISSUES! Many API methods are not responding as expected.\n";
}

echo "\n=== Complete GET Methods Test Finished ===\n";