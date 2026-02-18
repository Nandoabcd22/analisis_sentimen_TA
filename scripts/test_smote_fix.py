#!/usr/bin/env python3
"""
Test SMOTE fix with database connection
"""
import sys
import json
import sqlite3
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent))

from smote_processor import ProperSMOTE

def get_reviews_from_db():
    """Connect to Laravel database and get reviews"""
    try:
        # Try MySQL connection
        import mysql.connector
        
        conn = mysql.connector.connect(
            host='127.0.0.1',
            user='root',
            password='',
            database='analisis_sentimen_ta'
        )
        
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT id, label, stemming 
            FROM reviews 
            ORDER BY id ASC
        """)
        
        reviews = []
        for row in cursor.fetchall():
            try:
                stemming_list = json.loads(row['stemming']) if isinstance(row['stemming'], str) else row['stemming']
            except:
                stemming_list = row.get('stemming', '').split() if row.get('stemming') else []
            
            reviews.append({
                'id': str(row['id']),
                'label': row['label'],
                'stemming': stemming_list
            })
        
        cursor.close()
        conn.close()
        
        return reviews
    except Exception as e:
        print(f"MySQL connection failed: {e}")
        return None

def main():
    print("Testing SMOTE with real database reviews")
    print("="*70)
    
    reviews = get_reviews_from_db()
    
    if not reviews:
        print("Could not connect to database. Trying SQLite...")
        # Try SQLite (Laravel's test database sometimes uses SQLite)
        try:
            db_path = Path(__file__).parent.parent / 'database' / 'database.sqlite'
            if db_path.exists():
                conn = sqlite3.connect(str(db_path))
                conn.row_factory = sqlite3.Row
                cursor = conn.cursor()
                cursor.execute("SELECT id, label, stemming FROM reviews ORDER BY id ASC")
                
                reviews = []
                for row in cursor.fetchall():
                    try:
                        stemming_list = json.loads(row['stemming']) if isinstance(row['stemming'], str) else row['stemming']
                    except:
                        stemming_list = row['stemming'].split() if row['stemming'] else []
                    
                    reviews.append({
                        'id': str(row['id']),
                        'label': row['label'],
                        'stemming': stemming_list
                    })
                
                cursor.close()
                conn.close()
        except Exception as e:
            print(f"SQLite failed too: {e}")
    
    if not reviews:
        print("ERROR: Could not load reviews from database")
        print("\nTroubleshooting:")
        print("1. Check if database exists")
        print("2. Verify database credentials in .env file")
        print("3. Run: php artisan migrate")
        print("4. Check database/database.sqlite or MySQL connection")
        return
    
    print(f"Loaded {len(reviews)} reviews from database")
    
    # Calculate distribution
    dist = {}
    for r in reviews:
        label = r['label']
        dist[label] = dist.get(label, 0) + 1
    
    print(f"Original distribution: {dist}")
    print(f"Total: {sum(dist.values())}")
    
    # Run SMOTE
    print("\n" + "="*70)
    print("Running SMOTE with all-class balancing...")
    print("="*70)
    
    smote = ProperSMOTE(k_neighbors=3)
    result = smote.apply_smote(reviews)
    
    if result['success']:
        stats = result['statistics']
        
        print(f"\nAfter SMOTE: {stats['new_distribution']}")
        print(f"Synthetic generated: {stats['synthetic_generated']}")
        print(f"Total samples: {stats['total_samples']}")
        
        # Verify balance
        new_dist = stats['new_distribution']
        values = list(new_dist.values())
        all_equal = len(set(values)) == 1
        
        print(f"\n{'='*70}")
        if all_equal:
            print(f"SUCCESS: All classes perfectly balanced at {values[0]} each")
        else:
            min_val = min(values)
            max_val = max(values)
            diff = max_val - min_val
            print(f"Classes: {new_dist}")
            print(f"Min: {min_val}, Max: {max_val}, Difference: {diff}")
            if diff <= 1:
                print("SUCCESS: Nearly balanced (difference <= 1)")
            else:
                print(f"WARNING: Imbalance detected (difference: {diff})")
        print("="*70)
        
        # Save results
        output_file = Path(__file__).parent / 'smote_test_results.json'
        with open(output_file, 'w') as f:
            json.dump({
                'original_distribution': stats['original_distribution'],
                'new_distribution': stats['new_distribution'],
                'synthetic_generated': stats['synthetic_generated'],
                'total_samples': stats['total_samples'],
                'balanced': all_equal or (max_val - min_val <= 1)
            }, f, indent=2)
        print(f"\nResults saved to: {output_file}")
        
    else:
        print(f"FAILED: {result.get('message', result.get('error'))}")

if __name__ == '__main__':
    main()
