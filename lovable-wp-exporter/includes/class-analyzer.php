<?php
/**
 * Analyzer Class
 * 
 * Analiza código fuente de React/JSX y extrae componentes, estilos y estructura
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Analyzer {
    
    /**
     * Parse source code and extract components
     */
    public function parse($source_code, $source_type = 'lovable') {
        $result = array(
            'components' => array(),
            'styles' => array(),
            'assets' => array(),
            'structure' => array(),
            'metadata' => array(
                'source_type' => $source_type,
                'parsed_at' => current_time('mysql'),
                'total_components' => 0,
            )
        );
        
        // Parse based on source type
        switch ($source_type) {
            case 'lovable':
            case 'bolt':
            case 'base44':
                $result = $this->parse_react_components($source_code, $result);
                break;
            case 'file':
                $result = $this->parse_file_upload($source_code, $result);
                break;
        }
        
        // Extract Tailwind classes
        $result['styles']['tailwind'] = $this->extract_tailwind_classes($source_code);
        
        // Extract imports and dependencies
        $result['metadata']['dependencies'] = $this->extract_dependencies($source_code);
        
        // Count components
        $result['metadata']['total_components'] = count($result['components']);
        
        return $result;
    }
    
    /**
     * Parse React components from source code
     */
    private function parse_react_components($source_code, &$result) {
        // Match ES6 arrow function components
        $pattern_arrow = '/(?:export\s+)?(?:const|function)\s+([A-Z][a-zA-Z0-9_]*)\s*=\s*\(([^)]*)\)\s*=>\s*(?:{)?/';
        
        // Match traditional function components
        $pattern_function = '/(?:export\s+)?function\s+([A-Z][a-zA-Z0-9_]*)\s*\(([^)]*)\)/';
        
        // Match React.memo components
        $pattern_memo = '/(?:export\s+)?(?:const)\s+([A-Z][a-zA-Z0-9_]*)\s*=\s*React\.memo/';
        
        $patterns = array($pattern_arrow, $pattern_function, $pattern_memo);
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $source_code, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $component_name = $match[1];
                    $component_props = isset($match[2]) ? $this->parse_props($match[2]) : array();
                    
                    // Extract component body
                    $component_body = $this->extract_component_body($source_code, $component_name);
                    
                    // Extract JSX structure
                    $jsx_structure = $this->parse_jsx($component_body);
                    
                    // Identify component type
                    $component_type = $this->identify_component_type($component_body);
                    
                    $result['components'][$component_name] = array(
                        'name' => $component_name,
                        'props' => $component_props,
                        'body' => $component_body,
                        'jsx' => $jsx_structure,
                        'type' => $component_type,
                        'tailwind_classes' => $this->extract_tailwind_classes($component_body),
                        'imports' => $this->extract_imports($component_body),
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Parse file upload (ZIP or directory)
     */
    private function parse_file_upload($file_path, &$result) {
        if (!file_exists($file_path)) {
            return $result;
        }
        
        // If it's a ZIP, extract it
        if (pathinfo($file_path, PATHINFO_EXTENSION) === 'zip') {
            $extract_path = dirname($file_path) . '/extracted';
            $zip = new ZipArchive();
            
            if ($zip->open($file_path) === TRUE) {
                $zip->extractTo($extract_path);
                $zip->close();
                $file_path = $extract_path;
            }
        }
        
        // Scan for React files
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($file_path)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), array('tsx', 'jsx', 'ts', 'js'))) {
                $source_code = file_get_contents($file->getPathname());
                $result = $this->parse_react_components($source_code, $result);
                
                // Track assets
                if (in_array($file->getExtension(), array('png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'))) {
                    $result['assets'][] = array(
                        'path' => $file->getPathname(),
                        'name' => $file->getFilename(),
                        'type' => 'image',
                    );
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Parse component props
     */
    private function parse_props($props_string) {
        $props = array();
        
        // Destructuring pattern: { prop1, prop2, prop3 = defaultValue }
        if (preg_match('/{([^}]+)}/', $props_string, $match)) {
            $prop_list = explode(',', $match[1]);
            foreach ($prop_list as $prop) {
                $prop = trim($prop);
                if (strpos($prop, '=') !== false) {
                    list($name, $default) = explode('=', $prop);
                    $props[trim($name)] = array(
                        'name' => trim($name),
                        'default' => trim($default),
                        'required' => false,
                    );
                } else {
                    $props[$prop] = array(
                        'name' => $prop,
                        'default' => null,
                        'required' => true,
                    );
                }
            }
        }
        
        return $props;
    }
    
    /**
     * Extract component body from source code
     */
    private function extract_component_body($source_code, $component_name) {
        $body = '';
        
        // Find the component declaration
        $patterns = array(
            "/(?:export\s+)?const\s+{$component_name}\s*=\s*\([^)]*\)\s*=>\s*{/",
            "/(?:export\s+)?function\s+{$component_name}\s*\([^)]*\)\s*{/",
            "/(?:export\s+)?const\s+{$component_name}\s*=\s*React\.memo\([^)]*\)\s*=>\s*{/",
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $source_code, $match, PREG_OFFSET_CAPTURE)) {
                $start_pos = $match[0][1] + strlen($match[0][0]);
                $brace_count = 1;
                $pos = $start_pos;
                
                while ($brace_count > 0 && $pos < strlen($source_code)) {
                    $char = $source_code[$pos];
                    
                    if ($char === '{') {
                        $brace_count++;
                    } elseif ($char === '}') {
                        $brace_count--;
                    }
                    
                    $pos++;
                }
                
                $body = substr($source_code, $start_pos, $pos - $start_pos - 1);
                break;
            }
        }
        
        return $body;
    }
    
    /**
     * Parse JSX structure
     */
    private function parse_jsx($jsx_string) {
        $structure = array(
            'root_elements' => array(),
            'nested_elements' => array(),
            'text_content' => array(),
        );
        
        // Match HTML/JSX tags
        $tag_pattern = '/<([a-zA-Z][a-zA-Z0-9]*)([^>]*)(?:\/>|>(.*?)<\/\1>)/s';
        
        if (preg_match_all($tag_pattern, $jsx_string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tag_name = $match[1];
                $attributes = $this->parse_attributes($match[2]);
                $content = isset($match[4]) ? trim($match[4]) : '';
                
                $element = array(
                    'tag' => $tag_name,
                    'attributes' => $attributes,
                    'content' => $content,
                    'children' => array(),
                );
                
                $structure['root_elements'][] = $element;
                
                if (!empty($content)) {
                    $structure['text_content'][] = $content;
                }
            }
        }
        
        return $structure;
    }
    
    /**
     * Parse HTML/JSX attributes
     */
    private function parse_attributes($attr_string) {
        $attributes = array();
        
        // Match attribute patterns: name="value" or name={'expression'}
        $attr_pattern = '/([a-zA-Z][a-zA-Z0-9\-_]*)(?:\s*=\s*(?:"([^"]*)"|{([^}]+)}))?/';
        
        if (preg_match_all($attr_pattern, $attr_string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1];
                $value = isset($match[2]) && $match[2] !== '' ? $match[2] : (isset($match[3]) ? $match[3] : null);
                $attributes[$name] = $value;
            }
        }
        
        return $attributes;
    }
    
    /**
     * Identify component type based on content
     */
    private function identify_component_type($body) {
        $type = 'generic';
        
        // Check for common patterns
        if (strpos($body, 'nav') !== false || strpos($body, 'Nav') !== false) {
            $type = 'navigation';
        } elseif (strpos($body, 'footer') !== false || strpos($body, 'Footer') !== false) {
            $type = 'footer';
        } elseif (strpos($body, 'header') !== false || strpos($body, 'Header') !== false) {
            $type = 'header';
        } elseif (strpos($body, 'hero') !== false || strpos($body, 'Hero') !== false) {
            $type = 'hero';
        } elseif (strpos($body, 'card') !== false || strpos($body, 'Card') !== false) {
            $type = 'card';
        } elseif (strpos($body, 'button') !== false || strpos($body, 'Button') !== false) {
            $type = 'button';
        } elseif (strpos($body, 'form') !== false || strpos($body, 'Form') !== false) {
            $type = 'form';
        } elseif (strpos($body, 'modal') !== false || strpos($body, 'Modal') !== false) {
            $type = 'modal';
        }
        
        return $type;
    }
    
    /**
     * Extract Tailwind CSS classes
     */
    public function extract_tailwind_classes($source_code) {
        $classes = array();
        
        // Match className="..." or className='...'
        $pattern = '/className\s*=\s*["\']([^"\']+)["\']/';
        
        if (preg_match_all($pattern, $source_code, $matches)) {
            foreach ($matches[1] as $class_string) {
                $class_list = explode(' ', $class_string);
                foreach ($class_list as $class) {
                    $class = trim($class);
                    if (!empty($class) && strpos($class, '{') === false) {
                        $classes[$class] = true;
                    }
                }
            }
        }
        
        // Match class names in template literals: className={`...`}
        $template_pattern = '/className\s*=\s*{`([^`]+)`}/';
        
        if (preg_match_all($template_pattern, $source_code, $matches)) {
            foreach ($matches[1] as $class_string) {
                $class_list = explode(' ', $class_string);
                foreach ($class_list as $class) {
                    $class = trim($class);
                    if (!empty($class) && strpos($class, '$') === false) {
                        $classes[$class] = true;
                    }
                }
            }
        }
        
        return array_keys($classes);
    }
    
    /**
     * Extract import statements
     */
    private function extract_imports($source_code) {
        $imports = array();
        
        $pattern = '/import\s+(?:(?:(\*\s+as\s+\w+)|(\w+)|({[^}]+}))\s+from\s+)?["\']([^"\']+)["\']/';
        
        if (preg_match_all($pattern, $source_code, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $import = array(
                    'source' => $match[5],
                    'default' => !empty($match[2]) ? $match[2] : null,
                    'named' => !empty($match[3]) ? $this->parse_named_imports($match[3]) : array(),
                    'namespace' => !empty($match[1]) ? $match[1] : null,
                );
                
                $imports[] = $import;
            }
        }
        
        return $imports;
    }
    
    /**
     * Parse named imports
     */
    private function parse_named_imports($import_string) {
        $named = array();
        $parts = explode(',', $import_string);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ' as ') !== false) {
                list($original, $alias) = explode(' as ', $part);
                $named[trim($alias)] = trim($original);
            } else {
                $named[$part] = $part;
            }
        }
        
        return $named;
    }
    
    /**
     * Extract dependencies from imports
     */
    private function extract_dependencies($source_code) {
        $dependencies = array(
            'react' => false,
            'react_dom' => false,
            'lucide' => false,
            'shadcn' => false,
            'custom' => array(),
        );
        
        if (strpos($source_code, "from 'react'") !== false || strpos($source_code, 'from "react"') !== false) {
            $dependencies['react'] = true;
        }
        
        if (strpos($source_code, "lucide-react") !== false) {
            $dependencies['lucide'] = true;
        }
        
        if (strpos($source_code, "@/components/ui") !== false) {
            $dependencies['shadcn'] = true;
        }
        
        // Extract custom component imports
        $custom_pattern = '/import\s+.*\s+from\s+["\'](@\/components\/[^"\']+)["\']/';
        
        if (preg_match_all($custom_pattern, $source_code, $matches)) {
            $dependencies['custom'] = array_unique($matches[1]);
        }
        
        return $dependencies;
    }
}
