#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
DIAGNOSTIC: Measure pure preprocessing speed vs total time reported in Laravel
"""

import sys
import json
import time
import subprocess
from pathlib import Path

print("=" * 70)
print("PREPROCESSING PERFORMANCE DIAGNOSTIC")
print("=" * 70)

# Test berbagai dataset sizes
test_sizes = [1000, 5000, 10000, 50000, 100000]

print("\n📊 PURE PREPROCESSING SPEED TEST\n")
print(f"{'Records':>10} | {'Time (s)':>12} | {'Rate (rec/s)':>15}")
print("-" * 40)

for size in test_sizes:
    # Create test batch
    batch = []
    for i in range(1, size + 1):
        batch.append({
            'id': i,
            'text': 'Produk bagusnya berkualitas tinggi harga terjangkau'
        })
    
    test_file = f'test_diag_{size}.json'
    with open(test_file, 'w', encoding='utf-8') as f:
        json.dump(batch, f, ensure_ascii=False)
    
    # Measure preprocessing time
    start = time.time()
    result = subprocess.run(
        ['python', 'scripts/preprocessing.py', '--batch', test_file],
        capture_output=True,
        text=True,
        timeout=300
    )
    elapsed = time.time() - start
    
    if result.returncode == 0:
        rate = int(size / elapsed)
        print(f"{size:>10} | {elapsed:>12.2f} | {rate:>15}")
    else:
        print(f"{size:>10} | ERROR: {result.stderr[:50]}")
    
    # Cleanup
    Path(test_file).unlink(missing_ok=True)

print("\n" + "=" * 70)
print("ANALYSIS")
print("=" * 70)

print("""
If preprocessing times above are < estimated times below, then:
✅ PREPROCESSING is FAST (our optimization works!)
❌ BOTTLENECK is elsewhere (database, PHP overhead, etc)

Expected times for reference:
- 100K records: ~13 seconds max
- 500K records: ~65 seconds max

If your Laravel reports:
- 4 minutes+ for 100K → Database or PHP overhead is slow!
- 4 minutes+ for 500K → Expected (similar to above)
- 4 minutes+ for 50K  → Definitely non-preprocessing bottleneck!

Next: Check DashboardController `batchUpdateReviews()` function
        May need to optimize database batch updates instead.
""")
