import { Router } from 'express';
import multer from 'multer';
import JSZip from 'jszip';
import { AuthRequest } from '../middleware/auth.middleware';

const router = Router();
const upload = multer({ storage: multer.memoryStorage() });

// Convert ZIP to WordPress components
router.post('/convert', upload.single('file'), async (req: AuthRequest, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ error: 'No file uploaded' });
    }
    
    // Read ZIP file
    const zip = await JSZip.loadAsync(req.file.buffer);
    
    const components: any[] = [];
    const images: any[] = [];
    const lucideIcons = new Set<string>();
    const fonts = new Set<string>();
    
    // Process files
    const promises: Promise<void>[] = [];
    
    zip.forEach((path, entry) => {
      if (entry.dir) return;
      
      // Process React components
      if (path.match(/(src\/)?(components|pages)\/.*\.(tsx|jsx)$/i)) {
        promises.push(
          entry.async('string').then(content => {
            const name = path.split('/').pop()?.replace('.tsx', '') || 'Component';
            
            // Extract Lucide icons
            const lucideMatch = content.match(/import\s*{\s*([^}]+)\s*}\s*from\s*['"]lucide-react['"]/);
            if (lucideMatch) {
              lucideMatch[1].split(',').forEach(icon => lucideIcons.add(icon.trim()));
            }
            
            // Extract fonts
            const fontMatches = content.matchAll(/fontFamily:\s*['"]([^'"]+)['"]/g);
            for (const match of fontMatches) {
              fonts.add(match[1]);
            }
            
            // Convert to HTML
            const html = convertReactToHtml(content);
            const css = extractTailwind(content);
            
            components.push({
              name,
              path,
              html,
              css,
              icons: Array.from(lucideIcons),
            });
          })
        );
      }
      
      // Process images
      if (path.match(/\.(png|jpg|jpeg|gif|svg|webp)$/i)) {
        promises.push(
          entry.async('base64').then(base64 => {
            const ext = path.split('.').pop();
            const mimeType = `image/${ext === 'svg' ? 'svg+xml' : ext}`;
            images.push({
              name: path.split('/').pop(),
              path,
              base64: `data:${mimeType};base64,${base64}`,
            });
          })
        );
      }
    });
    
    await Promise.all(promises);
    
    // Generate full HTML and CSS
    const fullHtml = components.map(c => c.html).join('\n\n');
    const fullCss = components.map(c => c.css).join('\n\n');
    const animations = generateAnimations();
    
    res.json({
      success: true,
      components,
      images,
      lucideIcons: Array.from(lucideIcons),
      fonts: Array.from(fonts),
      html: fullHtml,
      css: fullCss + '\n\n' + animations,
    });
  } catch (error) {
    console.error('Conversion error:', error);
    res.status(500).json({ error: 'Conversion failed' });
  }
});

// Helper functions (simplified versions)
function convertReactToHtml(code: string): string {
  let html = code;
  
  // Extract return statement
  const returnMatch = html.match(/return\s*\(([\s\S]*)\)\s*;?\s*$/m);
  if (returnMatch) {
    html = returnMatch[1];
  }
  
  // Remove motion.* and convert to CSS classes
  html = html.replace(/<motion\./g, '<').replace(/<\/motion\./g, '</');
  html = html.replace(/initial\s*=\s*{[^}]*}\s*/g, '');
  html = html.replace(/animate\s*=\s*{[^}]*}/g, ' class="animate-fade-in" ');
  html = html.replace(/transition\s*=\s*{[^}]*}\s*/g, '');
  
  // Remove imports/exports
  html = html.replace(/import\s+.*?from\s+['"].*?['"];?\s*/g, '');
  html = html.replace(/export\s+default\s+\w+;?\s*/g, '');
  
  // Remove JSX expressions
  html = html.replace(/\{[^{}]*\}/g, '');
  
  // Remove event handlers
  html = html.replace(/\s+onClick\s*=\s*{[^}]*}/g, '');
  html = html.replace(/\s+onChange\s*=\s*{[^}]*}/g, '');
  
  // Convert className to class
  html = html.replace(/className\s*=/g, 'class=');
  html = html.replace(/htmlFor\s*=/g, 'for=');
  
  // Clean up
  html = html.replace(/<>\s*/g, '').replace(/\s*<\/>/g, '');
  html = html.replace(/<(img|input|br|hr)([^>]*)(?<!\/)>/gi, '<$1$2 />');
  html = html.split('\n').filter(l => l.trim()).join('\n');
  
  return html.trim();
}

function extractTailwind(code: string): string {
  const classes = new Set<string>();
  const regex = /className\s*=\s*["']([^"']+)["']/g;
  let match;
  
  while ((match = regex.exec(code)) !== null) {
    match[1].split(/\s+/).forEach(c => {
      if (c.trim()) classes.add(c);
    });
  }
  
  // Simplified Tailwind map
  const map: Record<string, string> = {
    'flex': 'display: flex;',
    'flex-col': 'flex-direction: column;',
    'items-center': 'align-items: center;',
    'justify-center': 'justify-content: center;',
    'justify-between': 'justify-content: space-between;',
    'p-4': 'padding: 1rem;',
    'p-8': 'padding: 2rem;',
    'text-4xl': 'font-size: 2.25rem;',
    'text-xl': 'font-size: 1.25rem;',
    'font-bold': 'font-weight: 700;',
    'text-white': 'color: #ffffff;',
    'bg-blue-500': 'background-color: #3b82f6;',
    'rounded-lg': 'border-radius: 0.5rem;',
    'shadow-lg': 'box-shadow: 0 10px 15px rgba(0,0,0,0.1);',
    'w-full': 'width: 100%;',
    'h-full': 'height: 100%;',
    'container': 'max-width: 1280px; margin: 0 auto; padding: 0 1rem;',
    'gap-4': 'gap: 1rem;',
  };
  
  let css = '/* Tailwind conversions */\n';
  classes.forEach(cls => {
    if (map[cls]) {
      css += `.${cls} { ${map[cls]} }\n`;
    }
  });
  
  return css;
}

function generateAnimations(): string {
  return `
/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
`;
}

export default router;
