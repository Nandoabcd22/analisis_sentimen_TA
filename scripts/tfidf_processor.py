#!/usr/bin/env python3
"""
TF-IDF Processor - Main script called by Laravel DashboardController
Reads JSON input, calculates TF-IDF with proper formula, outputs results
"""

import json
import sys
import os

# Add parent directory to path to import tfidf_calculator
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from tfidf_calculator import process_tfidf_from_reviews


def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            'success': False,
            'error': 'Usage: python tfidf_processor.py <input_file> <output_file>'
        }))
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    try:
        # Read input data
        with open(input_file, 'r', encoding='utf-8') as f:
            reviews_data = json.load(f)
        
        # Process TF-IDF
        tfidf_results, statistics = process_tfidf_from_reviews(
            reviews_data,
            min_df=2,           # Term harus muncul minimal di 2 dokumen
            max_df_ratio=0.8,   # Term tidak boleh di >80% dokumen
            min_tfidf=0.001     # Filter term dengan score < 0.001
        )
        
        # Sort by TF-IDF score descending
        tfidf_results_sorted = sorted(tfidf_results, key=lambda x: x['tfidf_score'], reverse=True)
        
        # Prepare output
        output_data = {
            'success': True,
            'data': tfidf_results_sorted,
            'statistics': statistics
        }
        
        # Write output
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(output_data, f, ensure_ascii=False, indent=2)
        
        print(json.dumps({
            'success': True,
            'message': 'TF-IDF calculation completed',
            'total_features': len(tfidf_results_sorted),
            'statistics': statistics
        }, ensure_ascii=False))
        
        sys.exit(0)
        
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': str(e)
        }, ensure_ascii=False))
        sys.exit(1)


if __name__ == '__main__':
    main()
