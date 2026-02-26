#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Simple test to verify predictions are working
"""
import sys
import os

# Add scripts to path
sys.path.insert(0, os.path.join(os.path.dirname(__file__), 'scripts'))

from predict_sentiment import predict_sentiment

test_cases = {
    "pantai nya kotor": "Negatif",
    "pantai ini sangat indah": "Positif",
    "pantai ini dekat dari kota": "Netral",
}

print("\n========== PREDICTION ACCURACY TEST ==========\n")

passed = 0
failed = 0

for text, expected_sentiment in test_cases.items():
    result = predict_sentiment(text)
    
    if not result.get('success'):
        print(f"❌ FAILED: {text}")
        print(f"   Error: {result.get('error')}\n")
        failed += 1
        continue
    
    sentiment = result['sentiment']
    confidence = result['confidence']
    match = "✅" if sentiment == expected_sentiment else "❌"
    
    print(f"{match} Text: \"{text}\"")
    print(f"   Expected: {expected_sentiment}, Got: {sentiment}")
    print(f"   Confidence: {confidence*100:.2f}%")
    
    if result.get('debug_info'):
        debug = result['debug_info']
        print(f"   Preprocessing:")
        print(f"     - Case Folding: \"{debug.get('case_folding', '')}\"")
        print(f"     - After Stopword: {debug.get('after_stopword', [])}")
        print(f"     - Stemmed: \"{debug.get('stemmed', '')}\"")
    print()
    
    if sentiment == expected_sentiment:
        passed += 1
    else:
        failed += 1

print(f"\n========== RESULTS ==========")
print(f"✅ Passed: {passed}/{len(test_cases)}")
print(f"❌ Failed: {failed}/{len(test_cases)}")
print(f"Accuracy: {(passed/len(test_cases)*100):.1f}%\n")
