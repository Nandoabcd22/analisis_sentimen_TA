"""
Wordcloud Generation Utility
Generates wordcloud images from text data
"""

import io
import base64
from pathlib import Path

try:
    from wordcloud import WordCloud
    WORDCLOUD_AVAILABLE = True
except ImportError:
    WORDCLOUD_AVAILABLE = False


def generate_wordcloud_image(text, width=1200, height=600, background_color='white'):
    """
    Generate wordcloud image from text and return as base64 PNG
    
    Args:
        text (str): Input text to generate wordcloud from
        width (int): Image width in pixels
        height (int): Image height in pixels
        background_color (str): Background color
        
    Returns:
        dict: {'success': bool, 'image': base64_str or None, 'error': str or None}
    """
    if not WORDCLOUD_AVAILABLE:
        return {
            'success': False,
            'image': None,
            'error': 'WordCloud library not available. Install with: pip install wordcloud pillow'
        }
    
    try:
        if not text or len(text.strip()) == 0:
            return {
                'success': False,
                'image': None,
                'error': 'Empty text provided'
            }
        
        # Generate wordcloud
        wordcloud = WordCloud(
            width=width,
            height=height,
            background_color=background_color,
            colormap='viridis',
            max_words=100,
            relative_scaling=0.5,
            min_font_size=10
        ).generate(text)
        
        # Convert to image and then to base64
        image = wordcloud.to_image()
        
        # Save to bytes buffer
        buffer = io.BytesIO()
        image.save(buffer, format='PNG')
        buffer.seek(0)
        
        # Encode to base64
        image_base64 = base64.b64encode(buffer.getvalue()).decode('utf-8')
        
        return {
            'success': True,
            'image': image_base64,
            'error': None
        }
        
    except Exception as e:
        return {
            'success': False,
            'image': None,
            'error': f'Error generating wordcloud: {str(e)}'
        }


def generate_wordcloud_file(text, output_path, width=1200, height=600, background_color='white'):
    """
    Generate wordcloud image and save to file
    
    Args:
        text (str): Input text
        output_path (str): Path to save PNG file
        width (int): Image width
        height (int): Image height
        background_color (str): Background color
        
    Returns:
        dict: {'success': bool, 'path': str or None, 'error': str or None}
    """
    if not WORDCLOUD_AVAILABLE:
        return {
            'success': False,
            'path': None,
            'error': 'WordCloud library not available'
        }
    
    try:
        if not text or len(text.strip()) == 0:
            return {
                'success': False,
                'path': None,
                'error': 'Empty text provided'
            }
        
        # Generate wordcloud
        wordcloud = WordCloud(
            width=width,
            height=height,
            background_color=background_color,
            colormap='viridis',
            max_words=100,
            relative_scaling=0.5,
            min_font_size=10
        ).generate(text)
        
        # Ensure output directory exists
        Path(output_path).parent.mkdir(parents=True, exist_ok=True)
        
        # Save image
        wordcloud.to_file(output_path)
        
        return {
            'success': True,
            'path': output_path,
            'error': None
        }
        
    except Exception as e:
        return {
            'success': False,
            'path': None,
            'error': f'Error saving wordcloud: {str(e)}'
        }
