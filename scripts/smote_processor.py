#!/usr/bin/env python3
"""
Proper SMOTE Implementation for Text Sentiment Analysis
Uses TF-IDF feature space and imbalanced-learn library
"""

import json
import numpy as np
import pandas as pd
from collections import Counter
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.neighbors import NearestNeighbors
import sys
import os


class ProperSMOTE:
    """
    SMOTE implementation in TF-IDF feature space for text
    Proper method: interpolate features, not duplicate text
    """
    
    def __init__(self, k_neighbors=5, random_state=42):
        self.k_neighbors = k_neighbors
        self.random_state = random_state
        np.random.seed(random_state)
        
    def apply_smote(self, train_data, minority_class=None, sampling_strategy=1.0):
        """
        Apply SMOTE to training data
        
        Args:
            train_data: List of dicts with 'id', 'stemming', 'label'
            minority_class: Target minority class (None = balance all to majority)
            sampling_strategy: Ratio of synthetic samples (1.0 = balance to majority)
        
        Returns:
            Extended dataset with original + synthetic samples + metadata
        """
        
        if not train_data:
            return {'success': False, 'error': 'No training data provided'}
        
        # Extract TF-IDF features from stemming
        df = pd.DataFrame(train_data)
        
        # Convert stemming arrays to text for TF-IDF
        df['text'] = df['stemming'].apply(lambda x: ' '.join(x) if isinstance(x, list) else x)
        
        # Calculate TF-IDF
        vectorizer = TfidfVectorizer(max_features=500)
        X = vectorizer.fit_transform(df['text']).toarray()
        y = df['label'].values
        
        # Count samples per class
        class_counts = Counter(y)
        print(f"Original distribution: {dict(class_counts)}")
        
        # Find majority count
        majority_count = max(class_counts.values())
        target_count = int(majority_count * sampling_strategy)
        
        # If all classes are already balanced or close, skip SMOTE
        if all(count >= target_count * 0.95 for count in class_counts.values()):
            print("Data already balanced. Skipping SMOTE.")
            return {
                'success': True,
                'message': 'Data already balanced',
                'data': self._add_metadata(train_data, [], 0),
                'statistics': {
                    'original_distribution': dict(class_counts),
                    'synthetic_generated': 0,
                    'synthetic_samples': []
                }
            }
        
        print(f"Target count per class: {target_count}")
        
        # SMOTE for ALL minority classes (not just the smallest one)
        all_synthetic_samples = []
        all_synthetic_metadata = []
        total_synthetic = 0
        new_class_counts = class_counts.copy()
        
        for target_class in class_counts.keys():
            current_count = class_counts[target_class]
            
            # Skip if class is already at target
            if current_count >= target_count:
                print(f"'{target_class}' already at target ({current_count}/{target_count})")
                continue
            
            synthetic_needed = target_count - current_count
            print(f"Generating {synthetic_needed} synthetic samples for '{target_class}' ({current_count} -> {target_count})")
            
            # Get class samples
            class_indices = np.where(y == target_class)[0]
            class_data = [train_data[i] for i in class_indices]
            X_class = X[class_indices]
            
            # Find k-nearest neighbors within class
            k_neighbors = min(self.k_neighbors + 1, len(class_indices))
            knn = NearestNeighbors(n_neighbors=k_neighbors)
            knn.fit(X_class)
            distances, indices = knn.kneighbors(X_class)
            
            # Generate synthetic samples for this class
            for i in range(synthetic_needed):
                # Random sample from this class
                random_idx = np.random.randint(0, len(class_indices))
                
                # Random neighbor (excluding self at index 0)
                num_neighbors = len(indices[random_idx])
                if num_neighbors <= 1:
                    neighbor_idx = 0
                else:
                    neighbor_idx = np.random.randint(1, num_neighbors)
                
                neighbor_idx = indices[random_idx, neighbor_idx]
                
                # Interpolate in feature space
                lambda_val = np.random.random()
                synthetic_vector = X_class[random_idx] + lambda_val * (X_class[neighbor_idx] - X_class[random_idx])
                
                # Map back to features
                feature_names = vectorizer.get_feature_names_out()
                top_features = synthetic_vector.argsort()[-20:][::-1]
                synthetic_text = ' '.join([feature_names[idx] for idx in top_features if synthetic_vector[idx] > 0.01])
                
                # Create synthetic sample
                synthetic_sample = {
                    'id': f"synthetic_{target_class}_{i}_{random_idx}",
                    'original_id_1': class_data[random_idx]['id'],
                    'original_id_2': class_data[neighbor_idx]['id'],
                    'review': synthetic_text,
                    'label': target_class,
                    'stemming': synthetic_text.split(),
                    'is_synthetic': True,
                    'interpolation_ratio': round(lambda_val, 4)
                }
                
                all_synthetic_samples.append(synthetic_sample)
                all_synthetic_metadata.append({
                    'id': synthetic_sample['id'],
                    'generated_from': [class_data[random_idx]['id'], class_data[neighbor_idx]['id']],
                    'interpolation_ratio': lambda_val,
                    'label': target_class
                })
                
                total_synthetic += 1
            
            # Update count for this class
            new_class_counts[target_class] = target_count
        
        print(f"\nFinal distribution: {dict(new_class_counts)}")
        
        return {
            'success': True,
            'message': f'SMOTE completed: Generated {total_synthetic} synthetic samples',
            'data': self._add_metadata(train_data, all_synthetic_samples, total_synthetic),
            'statistics': {
                'original_distribution': dict(class_counts),
                'new_distribution': dict(new_class_counts),
                'minority_class': minority_class or 'All minority classes',
                'synthetic_generated': total_synthetic,
                'feature_space_used': 'TF-IDF',
                'k_neighbors': self.k_neighbors,
                'total_samples': {
                    'original': len(train_data),
                    'synthetic': total_synthetic,
                    'total': len(train_data) + total_synthetic
                },
                'synthetic_samples_metadata': all_synthetic_metadata
            }
        }
    
    def _add_metadata(self, original_data, synthetic_data, count):
        """Add is_synthetic flag to all samples"""
        result = []
        
        # Add original samples with metadata
        for sample in original_data:
            sample_copy = sample.copy()
            sample_copy['is_synthetic'] = False
            sample_copy['sample_type'] = 'original'
            result.append(sample_copy)
        
        # Add synthetic samples
        for sample in synthetic_data:
            result.append(sample)
        
        return result


def main():
    if len(sys.argv) < 3:
        print(json.dumps({
            'success': False,
            'error': 'Usage: python smote_processor.py <input_file> <output_file>'
        }))
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    try:
        # Read training data
        with open(input_file, 'r', encoding='utf-8') as f:
            train_data = json.load(f)
        
        print(f"Processing {len(train_data)} training samples...")
        
        # Apply SMOTE
        smote = ProperSMOTE(k_neighbors=5)
        result = smote.apply_smote(
            train_data,
            minority_class=None,  # Auto-detect
            sampling_strategy=1.0  # Balance to majority
        )
        
        # Write output
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(result, f, ensure_ascii=False, indent=2)
        
        print(f"SMOTE completed successfully!")
        print(json.dumps({
            'success': True,
            'message': result.get('message'),
            'synthetic_generated': result['statistics']['synthetic_generated'],
            'original_distribution': result['statistics']['original_distribution'],
            'new_distribution': result['statistics']['new_distribution']
        }, ensure_ascii=False))
        
        sys.exit(0)
        
    except Exception as e:
        error_msg = str(e)
        print(json.dumps({
            'success': False,
            'error': error_msg
        }, ensure_ascii=False))
        sys.exit(1)


if __name__ == '__main__':
    main()
