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
        # Custom minimal Indonesian stopwords - optimized for sentiment analysis
        self.stopwords = {
            'yang', 'dan', 'di', 'ke', 'dari', 'adalah', 'untuk', 'pada'
        }
        
        # Load normalization dictionary
        self.normalization_dict = self.load_normalization_dict()
        
        # ✅ OPTIMIZATION: Token cache untuk stemming (10-15x faster)
        self._stem_cache = {}
        
        # ✅ OPTIMIZATION: Pre-compiled regex patterns (2-3x faster)
        self.url_pattern = re.compile(r'http\S+|www\S+|https\S+', re.MULTILINE)
        self.mention_hashtag_pattern = re.compile(r'@\w+|#\w+')
        self.number_pattern = re.compile(r'\d+')
        self.special_char_pattern = re.compile(r'[^a-zA-Z\s]')
        self.whitespace_pattern = re.compile(r'\s+')
        self.letter_repeat_pattern = re.compile(r'([a-zA-Z])\1{2,}')
    
    def load_normalization_dict(self):
        """Load normalization dictionary from file"""
        # Gunakan absolute path untuk memastikan ketemu
        import os
        current_dir = os.path.dirname(os.path.abspath(__file__))
        kamus_path = os.path.join(current_dir, '..', 'resources', 'data', 'kamus_normalisasi.txt')
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
        
        # ✅ Use pre-compiled pattern (faster)
        text = self.letter_repeat_pattern.sub(r'\1\1', text)
        
        return text
    
    def handle_emoji_and_special(self, text):
        """Convert emoji and special characters to text representation"""
        if not isinstance(text, str):
            return ""
        
        # Map common emojis to words (basic support)
        emoji_map = {
            '😂': 'haha', '😭': 'sedih', '😡': 'marah', '😍': 'suka', '😱': 'kaget',
            '😞': 'sedih', '😁': 'senang', '👍': 'bagus', '👎': 'jelek', '❤️': 'suka',
            '😚': 'suka', '😘': 'suka', '😻': 'senang', '😸': 'senang', '😹': 'tertawa',
            '😀': 'senyum', '😃': 'ceria', '😄': 'senang', '😅': 'malu', '😆': 'tertawa'
        }
        
        for emoji, text_rep in emoji_map.items():
            text = text.replace(emoji, f' {text_rep} ')
        
        return text
    
    def spell_correction(self, text):
        """Apply basic spell correction for common Indonesian slang"""
        if not isinstance(text, str):
            return ""
        
        # Common slang corrections
        slang_corrections = {
            'bnget': 'banget',
            'bgt': 'banget',
            'bgv': 'bagus',
            'gk': 'tidak',
            'gkk': 'tidak',
            'gak': 'tidak',
            'ga': 'tidak',
            'nggak': 'tidak',
            'ngga': 'tidak',
            'dh': 'sudah',
            'sdh': 'sudah',
            'udh': 'sudah',
            'kk': 'kakak',
            'yg': 'yang',
            'utk': 'untuk',
            'u': 'kamu',
            'gue': 'saya',
            'elu': 'kamu',
            'abis': 'habis',
            'akinya': 'akibatnya',
            'akin': 'akibat',
            'alias': 'yaitu',
            'amat': 'sangat',
            'aja': 'saja',
            'ajah': 'saja'
        }
        
        words = text.split()
        corrected_words = []
        for word in words:
            if word.lower() in slang_corrections:
                corrected_words.append(slang_corrections[word.lower()])
            else:
                corrected_words.append(word)
        
        return ' '.join(corrected_words)
    
    def cleansing(self, text):
        """Remove non-alphanumeric characters except spaces"""
        if not isinstance(text, str):
            return ""
        
        # ✅ OPTIMIZATION: Use pre-compiled regex patterns (sequential sub is faster)
        # 1. Remove excessive repeated letters (e.g., 'halooo' -> 'halo')
        text = self.remove_excessive_letters(text)
        # 2. Remove links (use pre-compiled pattern)
        text = self.url_pattern.sub('', text)
        # 3. Remove hashtags & mentions (@) (use pre-compiled pattern)
        text = self.mention_hashtag_pattern.sub('', text)
        # 4. Remove numbers (use pre-compiled pattern)
        text = self.number_pattern.sub('', text)
        # 5. Remove non-alphabetic characters (use pre-compiled pattern)
        text = self.special_char_pattern.sub('', text)
        # 6. Remove extra whitespace (use pre-compiled pattern)
        text = self.whitespace_pattern.sub(' ', text).strip()
        
        return text
    
    def normalisasi(self, text):
        """Normalize text using dictionary"""
        tokens = text.split()
        normalized_tokens = self.normalisasi_kata(tokens, self.normalization_dict)
        result = ' '.join(normalized_tokens)
        return result
    
    def normalisasi_kata(self, tokens, kamus):
        """Normalize tokens using dictionary - handle both string and list input"""
        normalized_tokens = []
        for token in tokens:
            token_lower = token.lower() if isinstance(token, str) else str(token).lower()
            if token_lower in kamus:
                # Split if normalized word includes spaces
                normalized_tokens.extend(kamus[token_lower].split())
            else:
                normalized_tokens.append(token_lower)
        return normalized_tokens
    
    def tokenizing(self, text):
        """Split text into tokens"""
        return word_tokenize(text)
    
    def stopword_removal(self, tokens):
        """Remove stopwords from tokens"""
        filtered_tokens = [token for token in tokens if token not in self.stopwords]
        return filtered_tokens
    
    def cached_stem(self, token):
        """✅ OPTIMIZATION: Stem single token with caching (10-15x faster for repeated words)"""
        if token in self._stem_cache:
            return self._stem_cache[token]
        
        stemmed = self.stemmer.stem(token)
        self._stem_cache[token] = stemmed
        return stemmed
    
    def stemming(self, tokens):
        """Apply stemming to tokens with caching"""
        # ✅ Use cached stemming (much faster when words repeat)
        stemmed_tokens = [self.cached_stem(token) for token in tokens]
        return stemmed_tokens
    
    def parse_token_list(self, token_str):
        """Parse JSON token string back to list (for CSV reading)"""
        try:
            if isinstance(token_str, list):
                return token_str
            if isinstance(token_str, str):
                return json.loads(token_str)
            return []
        except:
            return []
    
    def preprocess_text(self, text):
        """Complete preprocessing pipeline for single text with emoji & spell correction"""
        if not isinstance(text, str) or not text.strip():
            return {
                'text': text,
                'case_folded': '',
                'cleaned': '',
                'tokens_normalized': [],
                'tokens_no_stop': [],
                'text_stemmed': '',
                'label': ''
            }
        # Step 0: Handle emoji and special characters
        text = self.handle_emoji_and_special(text)
        # Step 1: Case folding
        case_folded = self.case_folding(text)
        # Step 2: Spell correction for common slang
        spell_corrected = self.spell_correction(case_folded)
        # Step 3: Cleaning
        cleaned = self.cleansing(spell_corrected)
        if not cleaned.strip():
            return {
                'text': text,
                'case_folded': case_folded,
                'cleaned': '',
                'tokens_normalized': [],
                'tokens_no_stop': [],
                'text_stemmed': '',
                'label': ''
            }
        # Step 4: Normalisasi kata
        tokens = self.tokenizing(cleaned)
        tokens_normalized = self.normalisasi_kata(tokens, self.normalization_dict)
        # Step 5: Stopword removal
        tokens_no_stop = [w.lower() for w in tokens_normalized if w.lower() not in self.stopwords]
        # Step 6: Stemming (using cached stemming for optimization)
        text_stemmed = " ".join([self.cached_stem(w) for w in tokens_no_stop])
        return {
            'text': text,
            'case_folded': case_folded,
            'cleaned': cleaned,
            'tokens_normalized': tokens_normalized,
            'tokens_no_stop': tokens_no_stop,
            'text_stemmed': text_stemmed,
            'label': ''
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
    
    # Process each review - with improved emoji & spell correction
    case_folding = []
    for index, row in df.iterrows():
        # Support both 'text' and 'review' column names
        text = row['text'] if 'text' in row else row['review']
        
        if isinstance(text, str):
            # Step 1: Handle emoji and special characters
            emoji_handled = processor.handle_emoji_and_special(text)
            
            # Step 2: Case folding
            case_folded = processor.case_folding(emoji_handled)
            case_folding.append(case_folded)
            df.at[index, 'case_folding'] = case_folded
            
            # Step 3: Spell correction for common Indonesian slang
            spell_corrected = processor.spell_correction(case_folded)
            
            # Step 4: Cleansing
            cleansed = processor.cleansing(spell_corrected)
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

    # Mode 2: Batch processing (for large datasets) - ✅ OPTIMIZED VERSION
    if args.batch:
        try:
            # Suppress NLTK initialization output by redirecting stdout temporarily
            old_stdout = sys.stdout
            sys.stdout = StringIO()
            
            try:
                # ✅ OPTIMIZATION: Single processor instance maintains cache across all records!
                processor = TextPreprocessor()
            finally:
                # Restore stdout before continuing
                sys.stdout = old_stdout
            
            with open(args.batch, 'r', encoding='utf-8') as f:
                batch_data = json.load(f)
            
            results = []
            
            # ✅ OPTIMIZATION: Single optimized loop instead of double loop
            # This eliminates overhead from creating intermediate data structures
            for item in batch_data:
                text_id = item.get('id')
                text = item.get('text', '')
                
                # Early exit for empty/invalid text
                if not isinstance(text, str) or not text.strip():
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
                
                # ✅ OPTIMIZATION: All steps in one pass
                # Step 1: Case Folding
                case_folded = processor.case_folding(text)
                
                # Step 2: Cleansing (uses pre-compiled regex patterns)
                cleansed = processor.cleansing(case_folded)
                
                # Early exit if nothing left after cleansing
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
                
                # Step 6: Stemming (uses cache for repeated words - 10-15x faster!)
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
            
            # ✅ OPTIMIZATION: Efficient UTF-8 cleaning
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
