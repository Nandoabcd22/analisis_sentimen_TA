"""
TF-IDF Calculator dengan formula yang benar dan filtering
"""
import json
import math
from collections import Counter, defaultdict
from typing import List, Dict, Tuple


class TFIDFCalculator:
    def __init__(self, min_df=2, max_df_ratio=0.8, min_tfidf=0.001):
        """
        min_df: minimum document frequency (term harus muncul di minimal N dokumen)
        max_df_ratio: maximum document frequency ratio (term tidak boleh di > ratio x total_docs)
        min_tfidf: minimum TF-IDF score untuk disimpan
        """
        self.min_df = min_df
        self.max_df_ratio = max_df_ratio
        self.min_tfidf = min_tfidf
        self.total_docs = 0
        self.doc_freq = defaultdict(int)  # Jumlah dokumen yang berisi term
        self.category_doc_freq = defaultdict(lambda: defaultdict(int))  # Doc freq per category
        self.term_freq_per_doc = defaultdict(lambda: defaultdict(int))  # Term frequency dalam setiap doc
        
    def calculate_tfidf(self, reviews: List[Dict]) -> List[Dict]:
        """
        Calculate TF-IDF untuk semua reviews
        
        Args:
            reviews: List of dictionaries dengan keys: 'id', 'stemming', 'label'
                    stemming should be list of tokens
        
        Returns:
            List of dictionaries dengan TF-IDF scores dan statistics
        """
        self.total_docs = len(reviews)
        
        # Step 1: Count document frequency dan term frequency
        for review in reviews:
            stems = review.get('stemming', [])
            if isinstance(stems, str):
                stems = json.loads(stems) if stems else []
            
            label = review.get('label', '')
            unique_stems = set(stems)
            
            # Count term frequency dalam dokumen ini
            term_counts = Counter(stems)
            for term, freq in term_counts.items():
                term_lower = term.lower().strip()
                if term_lower:
                    self.term_freq_per_doc[review['id']][term_lower] = freq
            
            # Count document frequency
            for term in unique_stems:
                term_lower = term.lower().strip()
                if term_lower:
                    self.doc_freq[term_lower] += 1
                    self.category_doc_freq[label][term_lower] += 1
        
        # Step 2: Filter terms berdasarkan min_df dan max_df
        max_df = self.max_df_ratio * self.total_docs
        valid_terms = {
            term for term, df in self.doc_freq.items() 
            if self.min_df <= df <= max_df
        }
        
        # Step 3: Calculate TF-IDF untuk setiap term per category
        tfidf_data = []
        
        for review in reviews:
            stems = review.get('stemming', [])
            if isinstance(stems, str):
                stems = json.loads(stems) if stems else []
            
            label = review.get('label', '')
            total_terms_in_doc = len(stems)
            
            if total_terms_in_doc == 0:
                continue
            
            # Count term frequency dalam dokumen ini
            term_counts = Counter(stems)
            
            for term, term_freq in term_counts.items():
                term_lower = term.lower().strip()
                
                # Skip jika term tidak valid
                if term_lower not in valid_terms or not term_lower:
                    continue
                
                df = self.doc_freq[term_lower]
                
                # Calculate TF: term frequency / total terms in doc
                tf = term_freq / total_terms_in_doc
                
                # Calculate IDF: log(N / DF) dimana N = total docs
                idf = math.log(self.total_docs / df) if df > 0 else 0
                
                # TF-IDF score
                tfidf_score = tf * idf
                
                # Skip jika score terlalu rendah
                if tfidf_score < self.min_tfidf:
                    continue
                
                # Get category statistics
                category_df = self.category_doc_freq[label][term_lower]
                category_percentage = (category_df / self.total_docs) * 100 if self.total_docs > 0 else 0
                
                tfidf_data.append({
                    'review_id': review.get('id'),
                    'feature': term_lower,
                    'category': label,
                    'tf': round(tf, 6),
                    'idf': round(idf, 6),
                    'tfidf_score': round(tfidf_score, 6),
                    'term_frequency': term_freq,
                    'document_frequency': df,
                    'category_doc_frequency': category_df,
                    'category_percentage': round(category_percentage, 2)
                })
        
        return tfidf_data
    
    def get_top_features(self, tfidf_data: List[Dict], top_n=20) -> Dict:
        """
        Get top N features overall dan per category
        
        Args:
            tfidf_data: List of TF-IDF records
            top_n: Number of top features to return
        
        Returns:
            Dictionary dengan overall dan per-category top features
        """
        # Group by feature
        features = defaultdict(lambda: {'scores': [], 'categories': set()})
        category_features = defaultdict(list)
        
        for record in tfidf_data:
            feature = record['feature']
            categories = record['category']
            score = record['tfidf_score']
            
            features[feature]['scores'].append(score)
            features[feature]['categories'].add(categories)
            category_features[categories].append(record)
        
        # Calculate average TF-IDF per feature
        overall_features = [
            {
                'feature': feature,
                'avg_tfidf': round(sum(data['scores']) / len(data['scores']), 6),
                'categories': list(data['categories']),
                'appearances': len(data['scores'])
            }
            for feature, data in features.items()
        ]
        
        overall_features.sort(key=lambda x: x['avg_tfidf'], reverse=True)
        
        # Top features per category
        top_per_category = {}
        for category, records in category_features.items():
            sorted_records = sorted(records, key=lambda x: x['tfidf_score'], reverse=True)
            top_per_category[category] = sorted_records[:top_n]
        
        return {
            'overall_top': overall_features[:top_n],
            'top_per_category': top_per_category
        }
    
    def get_statistics(self, tfidf_data: List[Dict]) -> Dict:
        """
        Get comprehensive statistics tentang TF-IDF results
        """
        if not tfidf_data:
            return {}
        
        scores = [record['tfidf_score'] for record in tfidf_data]
        categories = defaultdict(int)
        unique_terms = set()
        
        for record in tfidf_data:
            categories[record['category']] += 1
            unique_terms.add(record['feature'])
        
        return {
            'total_records': len(tfidf_data),
            'unique_terms': len(unique_terms),
            'avg_tfidf': round(sum(scores) / len(scores), 6),
            'max_tfidf': round(max(scores), 6),
            'min_tfidf': round(min(scores), 6),
            'records_per_category': dict(categories),
            'total_docs': self.total_docs,
            'filtered_out_min_df': len([t for t, df in self.doc_freq.items() if df < self.min_df]),
            'filtered_out_max_df': len([t for t, df in self.doc_freq.items() if df > self.max_df_ratio * self.total_docs])
        }


def process_tfidf_from_reviews(reviews_data: List[Dict], min_df=2, max_df_ratio=0.8, min_tfidf=0.001) -> Tuple[List[Dict], Dict]:
    """
    Main function untuk process TF-IDF dari review data
    
    Args:
        reviews_data: List of review dictionaries
        min_df: Minimum document frequency
        max_df_ratio: Maximum document frequency ratio
        min_tfidf: Minimum TF-IDF threshold
    
    Returns:
        Tuple of (tfidf_results, statistics)
    """
    calculator = TFIDFCalculator(min_df=min_df, max_df_ratio=max_df_ratio, min_tfidf=min_tfidf)
    tfidf_data = calculator.calculate_tfidf(reviews_data)
    stats = calculator.get_statistics(tfidf_data)
    
    return tfidf_data, stats
