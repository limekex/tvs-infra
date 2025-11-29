#!/bin/bash
# Test script for Issue #21 Manual Activity endpoints

BASE_URL="http://localhost:8080"
API_URL="${BASE_URL}/wp-json/tvs/v1"

# Get nonce (requires logged-in session)
echo "=== Getting nonce ==="
# Login and get WordPress REST API nonce
LOGIN_RESPONSE=$(curl -s -c cookies.txt -X POST "${BASE_URL}/wp-login.php" \
    -d "log=admin&pwd=admin&wp-submit=Log+In&redirect_to=${BASE_URL}/wp-admin/&testcookie=1")

# Get nonce from admin page
NONCE=$(curl -s -b cookies.txt "${BASE_URL}/wp-admin/" | grep -o '"nonce":"[^"]*"' | head -1 | cut -d'"' -f4)

if [ -z "$NONCE" ]; then
    echo "❌ Failed to get nonce. Make sure WordPress is running and admin:admin works"
    rm -f cookies.txt
    exit 1
fi

echo "✅ Got nonce: $NONCE"
echo ""

# Test 1: Start manual activity
echo "=== Test 1: Start manual Run activity ==="
RESPONSE=$(curl -s -b cookies.txt -X POST "${API_URL}/activities/manual/start" \
    -H "Content-Type: application/json" \
    -H "X-WP-Nonce: ${NONCE}" \
    -d '{"type":"Run"}')

echo "$RESPONSE" | jq '.'

SESSION_ID=$(echo "$RESPONSE" | jq -r '.session_id')
echo ""
echo "✅ Session ID: $SESSION_ID"
echo ""

# Test 2: Update metrics
echo "=== Test 2: Update activity metrics ==="
sleep 2
RESPONSE=$(curl -s -b cookies.txt -X PATCH "${API_URL}/activities/manual/${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -H "X-WP-Nonce: ${NONCE}" \
    -d '{
        "elapsed_time": 300,
        "distance": 1.2,
        "speed": 10.5,
        "pace": 5.71,
        "incline": 2.5
    }')

echo "$RESPONSE" | jq '.'
echo ""

# Test 3: Update again (simulate progress)
echo "=== Test 3: Update metrics again ==="
sleep 2
RESPONSE=$(curl -s -b cookies.txt -X PATCH "${API_URL}/activities/manual/${SESSION_ID}" \
    -H "Content-Type: application/json" \
    -H "X-WP-Nonce: ${NONCE}" \
    -d '{
        "elapsed_time": 600,
        "distance": 2.4,
        "speed": 11.0,
        "pace": 5.45
    }')

echo "$RESPONSE" | jq '.'
echo ""

# Test 4: Finish activity
echo "=== Test 4: Finish and save activity ==="
RESPONSE=$(curl -s -b cookies.txt -X POST "${API_URL}/activities/manual/${SESSION_ID}/finish" \
    -H "Content-Type: application/json" \
    -H "X-WP-Nonce: ${NONCE}")

echo "$RESPONSE" | jq '.'

ACTIVITY_ID=$(echo "$RESPONSE" | jq -r '.activity_id')
PERMALINK=$(echo "$RESPONSE" | jq -r '.permalink')
echo ""
echo "✅ Activity created: ID=$ACTIVITY_ID"
echo "✅ Permalink: $PERMALINK"
echo ""

# Test 5: Upload to Strava (manual endpoint)
echo "=== Test 5: Upload manual activity to Strava ==="
echo "⚠️  This requires Strava connection. Skipping for now."
echo "To test manually:"
echo "curl -X POST \"${API_URL}/activities/${ACTIVITY_ID}/strava/manual\" \\"
echo "    -H \"X-WP-Nonce: ${NONCE}\" \\"
echo "    -b cookies.txt"
echo ""

# Cleanup
rm -f cookies.txt

echo "=== ✅ All tests completed! ==="
echo "Check activity at: $PERMALINK"
