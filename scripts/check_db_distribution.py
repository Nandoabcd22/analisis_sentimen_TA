#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Quick database vs Colab distribution check
"""
import mysql.connector
import json
from collections import Counter

try:
    conn = mysql.connector.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='analisis_sentimen_ta',
        port=3306
    )
    cursor = conn.cursor()
    cursor.execute("SELECT label FROM reviews WHERE label IN ('Positif', 'Netral', 'Negatif') ORDER BY id")
    rows = cursor.fetchall()
    conn.close()
    
    labels = [row[0] for row in rows]
    counts = Counter(labels)
    
    print(f"Total reviews in database: {len(labels)}")
    print(f"Distribution:")
    for label in ['Positif', 'Netral', 'Negatif']:
        print(f"  {label.lower()}: {counts.get(label, 0)}")
    
    print("\nColab expected:")
    print("  positif: 893")
    print("  netral: 775")
    print("  negatif: 180")
    
    # Check if match
    if counts.get('Positif', 0) == 893 and counts.get('Netral', 0) == 775 and counts.get('Negatif', 0) == 180:
        print("\n✓ Database MATCHES Colab distribution!")
    else:
        print("\n✗ Database DOES NOT match Colab!")
        
except Exception as e:
    print(f"Error: {e}")
