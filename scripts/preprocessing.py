# -*- coding: utf-8 -*-
import sys
import os

# Force UTF-8 encoding for stdout/stderr on Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

import pandas as pd
import re
import json
import nltk
from io import StringIO
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory

# Suppress NLTK download messages
os.environ['NLTK_DATA'] = os.path.expanduser('~/nltk_data')


class TextPreprocessor:
    def __init__(self):
        # Initialize stemmer and stopword remover
        stemmer_factory = StemmerFactory()
        self.stemmer = stemmer_factory.create_stemmer()
        # Download NLTK resources (hanya perlu sekali)
        try:
            nltk.data.find('tokenizers/punkt')
        except LookupError:
            nltk.download('punkt')
        try:
            nltk.data.find('tokenizers/punkt_tab')
        except LookupError:
            nltk.download('punkt_tab')
        try:
            nltk.data.find('corpora/stopwords')
        except LookupError:
            nltk.download('stopwords')
        # Get Indonesian stopwords
        self.stopwords = set(stopwords.words('indonesian'))
        
        # Load normalization dictionary
        self.normalization_dict = self.load_normalization_dict()
    
    def load_normalization_dict(self):
        """Load normalization dictionary from file"""
        # Gunakan absolute path untuk memastikan ketemu
        import os
        current_dir = os.path.dirname(os.path.abspath(__file__))
        kamus_path = os.path.join(current_dir, '..', 'kamus_normalisasi.txt')
        kamus = {}
        
        try:
            with open(kamus_path, 'r', encoding='utf-8') as file:
                for line in file:
                    line = line.strip().split('\t')
                    if len(line) == 2:
                        kata_asli = line[0]
                        kata_normalisasi = line[1]
                        kamus[kata_asli.lower()] = kata_normalisasi
        except FileNotFoundError:
            # Basic fallback normalization
            kamus = {
                'gak': 'tidak',
                'ga': 'tidak',
                'nggak': 'tidak',
                'bgt': 'banget',
                'dgn': 'dengan',
                'pd': 'pada',
                'yg': 'yang',
                'utk': 'untuk',
                'krn': 'karena',
                'tdk': 'tidak',
                'sdh': 'sudah',
                'blm': 'belum',
                'bs': 'bisa',
                'dpt': 'dapat',
                'jg': 'juga',
                'aja': 'saja',
                'sy': 'saya',
                'kmu': 'kamu',
                'dg': 'dengan',
                'dlm': 'dalam',
                'spt': 'seperti',
                'hingga': 'sampai',
                'sampe': 'sampai',
                'sampai': 'sampai',
                'karna': 'karena',
                'karenanya': 'karena',
                'utk': 'untuk',
                'untuk': 'untuk',
                'bhw': 'bahwa',
                'bahwa': 'bahwa',
                'shg': 'sehingga',
                'sehingga': 'sehingga',
                'jd': 'jadi',
                'jadi': 'jadi',
                'lbh': 'lebih',
                'lebih': 'lebih',
                'cm': 'cuma',
                'cuma': 'cuma',
                'hny': 'hanya',
                'hanya': 'hanya',
                'sm': 'sama',
                'sama': 'sama',
                'sprti': 'seperti',
                'seperti': 'seperti',
                'krna': 'karena',
                'karena': 'karena',
                'trus': 'terus',
                'terus': 'terus',
                'trmsk': 'termasuk',
                'termasuk': 'termasuk',
                'pnya': 'punya',
                'punya': 'punya',
                'msh': 'masih',
                'masih': 'masih',
                'msih': 'masih',
                'masa': 'masa',
                'masa': 'masa',
                'mna': 'mana',
                'mana': 'mana',
                'mn': 'mana',
                'mana': 'mana'
            }
        
        return kamus
    
    def case_folding(self, text):
        """Convert text to lowercase"""
        return text.lower()
    
    def remove_excessive_letters(self, text):
        """Remove excessive repeated letters (e.g., 'halooo' -> 'halo')"""
        if not isinstance(text, str):
            return ""
        
        # Replace 3 or more consecutive letters with 2 letters
        # Handles cases like 'halooo' -> 'halo', 'seeenak' -> 'senak'
        text = re.sub(r'([a-zA-Z])\1{2,}', r'\1\1', text)
        
        return text
    
    def cleansing(self, text):
        """Remove non-alphanumeric characters except spaces"""
        if not isinstance(text, str):
            return ""
        
        # 1. Remove excessive repeated letters (e.g., 'halooo' -> 'halo')
        text = self.remove_excessive_letters(text)
        # 2. Remove links
        text = re.sub(r'http\S+|www\S+|https\S+', '', text, flags=re.MULTILINE)
        # 3. Remove hashtags & mentions (@)
        text = re.sub(r'@\w+|#\w+', '', text)
        # 4. Remove numbers
        text = re.sub(r'\d+', '', text)
        # 5. Remove non-alphabetic characters (including emoji & symbols)
        text = re.sub(r'[^a-zA-Z\s]', '', text)
        # 6. Remove extra whitespace (multiple whitespace)
        text = re.sub(r'\s+', ' ', text).strip()
        
        return text
    
    def normalisasi(self, text):
        """Normalize text using dictionary"""
        tokens = text.split()
        normalized_tokens = []
        
        for token in tokens:
            if token.lower() in self.normalization_dict:
                # Split normalized value into multiple tokens if it contains spaces
                normalized_words = self.normalization_dict[token.lower()].split()
                normalized_tokens.extend(normalized_words)
            else:
                normalized_tokens.append(token)
        
        result = ' '.join(normalized_tokens)
        return result
    
    def tokenizing(self, text):
        """Split text into tokens"""
        return word_tokenize(text)
    
    def stopword_removal(self, tokens):
        """Remove stopwords from tokens"""
        filtered_tokens = [token for token in tokens if token not in self.stopwords]
        return filtered_tokens
    
    def stemming(self, tokens):
        """Apply stemming to tokens"""
        stemmed_tokens = [self.stemmer.stem(token) for token in tokens]
        return stemmed_tokens
    
    def preprocess_text(self, text):
        """Complete preprocessing pipeline for single text"""
        if not isinstance(text, str) or not text.strip():
            return {
                'original': text,
                'case_folding': '',
                'cleansing': '',
                'normalisasi': '',
                'tokenizing': [],
                'stopword': [],
                'stemming': [],
                'processed_text': ''
            }
        
        # Step 1: Case folding
        case_folded = self.case_folding(text)
        
        # Step 2: Cleansing
        cleansed = self.cleansing(case_folded)
        
        # Skip if cleansing results in empty text
        if not cleansed.strip():
            return {
                'original': text,
                'case_folding': case_folded,
                'cleansing': '',
                'normalisasi': '',
                'tokenizing': [],
                'stopword': [],
                'stemming': [],
                'processed_text': ''
            }
        
        # Step 3: Normalization
        normalized = self.normalisasi(cleansed)
        
        # Step 4: Tokenizing
        tokens = self.tokenizing(normalized)
        
        # Step 5: Stopword removal
        filtered_tokens = self.stopword_removal(tokens)
        
        # Step 6: Stemming
        stemmed_tokens = self.stemming(filtered_tokens)
        
        return {
            'original': text,
            'case_folding': case_folded,
            'cleansing': cleansed,
            'normalisasi': normalized,
            'tokenizing': tokens,
            'stopword': filtered_tokens,
            'stemming': stemmed_tokens,
            'processed_text': ' '.join(stemmed_tokens)
        }


def preprocess_csv(input_file, output_file):
    """Preprocess all reviews in CSV file - sesuai format Google Colab Anda"""
    processor = TextPreprocessor()
    
    # Read CSV
    df = pd.read_csv(input_file)
    
    print(f"Jumlah data: {df.shape[0]}")
    
    # Initialize new columns
    df['case_folding'] = ''
    df['cleansing'] = ''
    df['normalisasi'] = ''
    df['tokenizing'] = ''
    df['stopword'] = ''
    df['stemming'] = ''
    
    # Process each review - sesuai format Google Colab Anda
    case_folding = []
    for index, row in df.iterrows():
        # Support both 'text' and 'review' column names
        text = row['text'] if 'text' in row else row['review']
        
        if isinstance(text, str):
            # Case folding
            case_folded = processor.case_folding(text)
            case_folding.append(case_folded)
            df.at[index, 'case_folding'] = case_folded
            
            # Cleansing
            cleansed = processor.cleansing(case_folded)
            df.at[index, 'cleansing'] = cleansed
            
            # Normalization
            normalized = processor.normalisasi(cleansed)
            df.at[index, 'normalisasi'] = normalized
            
            # Tokenizing
            tokens = processor.tokenizing(normalized)
            df.at[index, 'tokenizing'] = json.dumps(tokens)
            
            # Stopword removal
            filtered_tokens = processor.stopword_removal(tokens)
            df.at[index, 'stopword'] = json.dumps(filtered_tokens)
            
            # Stemming
            stemmed_tokens = processor.stemming(filtered_tokens)
            df.at[index, 'stemming'] = json.dumps(stemmed_tokens)
        else:
            # Handle non-string data
            case_folding.append('')
            df.at[index, 'case_folding'] = ''
            df.at[index, 'cleansing'] = ''
            df.at[index, 'normalisasi'] = ''
            df.at[index, 'tokenizing'] = json.dumps([])
            df.at[index, 'stopword'] = json.dumps([])
            df.at[index, 'stemming'] = json.dumps([])       
        print(f"Processed row {index + 1}/{len(df)}")   
    # Assign case_folding using .loc (sesuai kode Anda)
    df.loc[:, 'case_folded'] = case_folding  
    # Display first 4 rows (sesuai kode Google Colab)
    print("\nPreview hasil preprocessing:")
    if 'text' in df.columns:
        display_cols = ['text', 'case_folded', 'case_folding', 'label']
    else:
        display_cols = ['review', 'case_folded', 'case_folding', 'label'] 
    print(df.loc[:4, display_cols].to_string())
    # Save to new CSV
    df.to_csv(output_file, index=False)
    print(f"\nPreprocessing completed! Output saved to: {output_file}")


def preprocess_single_text(text):
    """Preprocess single text for testing"""
    processor = TextPreprocessor()
    result = processor.preprocess_text(text)
    return result


if __name__ == "__main__":
    """CLI entry point

    Modes:
    - python preprocessing.py --text "your text"  -> prints JSON result to stdout
    - python preprocessing.py --in input.csv --out output.csv -> preprocess CSV
    If no args are provided, runs demo output as before.
    """
    import argparse
    import sys

    parser = argparse.ArgumentParser(description="Text preprocessing utilities")
    parser.add_argument("--text", type=str, help="Text to preprocess")
    parser.add_argument("--batch", type=str, help="JSON file with batch data to preprocess")
    parser.add_argument("--in", dest="input_csv", type=str, help="Input CSV path")
    parser.add_argument("--out", dest="output_csv", type=str, help="Output CSV path")
    args = parser.parse_args()

    # Mode 1: single text -> JSON to stdout
    if args.text is not None:
        try:
            res = preprocess_single_text(args.text)
            # Only keep fields needed by Laravel side
            payload = {
                "case_folding": res.get("case_folding", ""),
                "cleansing": res.get("cleansing", ""),
                "normalisasi": res.get("normalisasi", ""),
                "tokenizing": res.get("tokenizing", []),
                "stopword": res.get("stopword", []),
                "stemming": res.get("stemming", []),
            }
            
            # Clean all values to ensure valid UTF-8
            cleaned_payload = {}
            for key, value in payload.items():
                if isinstance(value, list):
                    cleaned_payload[key] = [str(item).encode('utf-8', errors='ignore').decode('utf-8') for item in value]
                else:
                    cleaned_payload[key] = str(value).encode('utf-8', errors='ignore').decode('utf-8')
            
            # Output JSON with proper UTF-8 handling
            print(json.dumps(cleaned_payload, ensure_ascii=True, separators=(',', ':')))
            sys.exit(0)
        except Exception as e:
            error_msg = str(e).encode('utf-8', errors='ignore').decode('utf-8')
            print(json.dumps({"error": error_msg}, ensure_ascii=True, separators=(',', ':')))
            sys.exit(1)

    # Mode 2: Batch processing (for large datasets)
    if args.batch:
        try:
            # Suppress NLTK initialization output by redirecting stdout temporarily
            old_stdout = sys.stdout
            sys.stdout = StringIO()
            
            try:
                processor = TextPreprocessor()
            finally:
                # Restore stdout before continuing
                sys.stdout = old_stdout
            
            with open(args.batch, 'r', encoding='utf-8') as f:
                batch_data = json.load(f)
            
            results = []
            
            # Step 1: Case Folding dengan looping
            case_folding_results = []
            for item in batch_data:
                text = item.get('text', '')
                if isinstance(text, str):
                    # Convert to lowercase (case folding)
                    data = text.lower()
                    case_folding_results.append({
                        'id': item.get('id'),
                        'text': text,
                        'case_folded': data
                    })
                else:
                    case_folding_results.append({
                        'id': item.get('id'),
                        'text': '',
                        'case_folded': ''
                    })
            
            # Process each case-folded text through remaining steps
            for item_cf in case_folding_results:
                text_id = item_cf['id']
                case_folded = item_cf['case_folded']
                
                if not case_folded or not case_folded.strip():
                    results.append({
                        'id': text_id,
                        'case_folding': '',
                        'cleansing': '',
                        'normalisasi': '',
                        'tokenizing': [],
                        'stopword': [],
                        'stemming': []
                    })
                    continue
                
                # Step 2: Cleansing
                cleansed = processor.cleansing(case_folded)
                
                if not cleansed or not cleansed.strip():
                    results.append({
                        'id': text_id,
                        'case_folding': case_folded,
                        'cleansing': '',
                        'normalisasi': '',
                        'tokenizing': [],
                        'stopword': [],
                        'stemming': []
                    })
                    continue
                
                # Step 3: Normalisasi
                normalized = processor.normalisasi(cleansed)
                
                # Step 4: Tokenizing
                tokens = processor.tokenizing(normalized)
                
                # Step 5: Stopword Removal
                filtered_tokens = processor.stopword_removal(tokens)
                
                # Step 6: Stemming
                stemmed_tokens = processor.stemming(filtered_tokens)
                
                results.append({
                    'id': text_id,
                    'case_folding': case_folded,
                    'cleansing': cleansed,
                    'normalisasi': normalized,
                    'tokenizing': tokens,
                    'stopword': filtered_tokens,
                    'stemming': stemmed_tokens
                })
            
            # Clean results for JSON output
            cleaned_results = []
            for result in results:
                cleaned_result = {}
                for key, value in result.items():
                    if isinstance(value, list):
                        cleaned_result[key] = [str(item).encode('utf-8', errors='ignore').decode('utf-8') for item in value]
                    else:
                        cleaned_result[key] = str(value).encode('utf-8', errors='ignore').decode('utf-8')
                cleaned_results.append(cleaned_result)
            
            # Output only JSON - nothing else
            print(json.dumps(cleaned_results, ensure_ascii=True, separators=(',', ':')))
            sys.exit(0)
        except Exception as e:
            error_msg = str(e).encode('utf-8', errors='ignore').decode('utf-8')
            print(json.dumps({"error": error_msg}, ensure_ascii=True, separators=(',', ':')))
            sys.exit(1)

    # Mode 3: CSV processing
    if args.input_csv and args.output_csv:
        preprocess_csv(args.input_csv, args.output_csv)
        sys.exit(0)

    # Default demo if no arguments provided
    input_csv = "data/reviews.csv"
    output_csv = "data/reviews_preprocessed.csv"
    print("=== Text Preprocessing Sentiment Analysis ===")
    print(f"Input file: {input_csv}")
    print(f"Output file: {output_csv}")
    print("-" * 50)
    print("\n=== Testing Single Text ===")
    test_text = "Tempatnya gak bgt dgn kl jg sdh"
    preprocess_single_text(test_text)
    print("\n" + "=" * 50)
    print("Processing CSV file...")
    # preprocess_csv(input_csv, output_csv)
    print("CSV processing skipped for testing.")
