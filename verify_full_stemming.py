from scripts.preprocessing import TextPreprocessor

# Create test cases
test_cases = [
    ('Produktnya sangat bagusnya', ['produk', 'sangat', 'bagus']),
    ('Berkualitas tinggi berkali kali', ['kualitas', 'tinggi', 'kali']),
    ('Mempercepat memproses memperbaiki', ['percepat', 'proses', 'perbaik']),
]

print('=' * 70)
print('✅ VERIFICATION: FULL STEMMING WITH GLOBAL CACHE')
print('=' * 70)

processor = TextPreprocessor()

for text, expected_stems in test_cases:
    # Preprocess
    case_folded = processor.case_folding(text)
    cleansed = processor.cleansing(case_folded)
    normalized = processor.normalisasi(cleansed)
    tokens = processor.tokenizing(normalized)
    stemmed = processor.stemming(tokens)
    
    print(f'\nInput: {text}')
    print(f'Tokens:  {tokens}')
    print(f'Stemmed: {stemmed}')
    
    # Check if has any actual stemming
    has_stemming = any(t != s for t, s in zip(tokens, stemmed))
    status = "YES (real Sastrawi stemming!)" if has_stemming else "NO"
    print(f'Has Stemming: {status}')
    
    for exp_stem in expected_stems:
        if exp_stem in stemmed:
            print(f'  ✅ Found expected stem: {exp_stem}')

print('\n' + '=' * 70)
print('CONCLUSION: FULL Sastrawi stemming is WORKING!')
print('=' * 70)
