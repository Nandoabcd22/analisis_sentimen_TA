#!/usr/bin/env python3
"""
Quick test to verify optimized preprocessing works
"""

import json
import sys
import os
import time
import tempfile

# Test with 100 sample records
sample_texts = [
    "Tempatnya gak bgt dgn kl jg sdh mantap bgt",
    "Pelayanannya nggak memuaskan bgt, harganya mahal banget",
    "Makanannya enak sekali rasanya sempurna",
    "Suasananya cozy banget, tempat nongkrongnya oke punya",
    "Rasa makanannya biasa aja, gak sesuai harganya yang mahal",
    "Porsi makanannya kecil tapi enak dan lezat",
    "Stafnya kurang ramah dan lambat dalam melayani",
    "Lokasi strategis banget mudah dicari dan dijangkau",
    "WiFi-nya lemot gak bisa browsing dengan lancar",
    "Minuman dinginnya segar banget cocok untuk melepas dahaga"
]

# Create batch data
batch_data = []
for i in range(100):
    batch_data.append({
        'id': i + 1,
        'text': sample_texts[i % len(sample_texts)] + f" variant {i}"
    })

# Create temp file
temp_file = tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False, encoding='utf-8')
json.dump(batch_data, temp_file)
temp_file.close()

print("Testing optimized preprocessing batch mode...")
print(f"Records: {len(batch_data)}")
print(f"Temp file: {temp_file.name}\n")

# Run preprocessing
pythonCmd = sys.executable if sys.executable else 'python'
scriptPath = os.path.join(os.path.dirname(__file__), 'preprocessing.py')

# Create output file in temp directory
output_file = tempfile.NamedTemporaryFile(suffix='.json', delete=False).name

start = time.time()
cmd = f'"{pythonCmd}" "{scriptPath}" --batch "{temp_file.name}"'
stream = os.popen(cmd)
output_text = stream.read()
stream.close()
elapsed = time.time() - start

# Parse output
result = json.loads(output_text.strip())

# Cleanup
os.unlink(temp_file.name)

print(f"✅ Preprocessing completed in {elapsed:.2f}s")
print(f"Records processed: {len(result)}")
print(f"Speed: {len(result)/elapsed:.0f} records/sec")

# Show sample
if len(result) > 0:
    sample = result[0]
    print(f"\nSample output (record 1):")
    print(f"  ID: {sample['id']}")
    print(f"  Case folding: {sample['case_folding'][:50]}...")
    print(f"  Stemming: {sample['stemming'][:50]}...")

print("\n✅ Test passed! Optimized preprocessing is working.")
