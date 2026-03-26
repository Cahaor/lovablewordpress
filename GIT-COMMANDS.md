# Comandos para subir el repositorio a GitHub

# 1. Navegar al directorio
cd "c:\Users\chaza\OneDrive - Carlos Hazañas Ortiz\360VM\Web\modern-market-makers-main"

# 2. Inicializar git (si no está inicializado)
git init

# 3. Añadir todos los archivos
git add .

# 4. Crear primer commit
git commit -m "Initial commit - Lovable WP Pro v1.0

Features:
- Backend API con Node.js + Express + Prisma
- Frontend React + Vite + Tailwind
- WordPress Plugin con Elementor integration
- Docker configuration para deploy
- CI/CD pipeline configurado
- Documentación completa"

# 5. Renombrar rama a main
git branch -M main

# 6. Añadir remote (si no está añadido)
git remote add origin https://github.com/Cahaor/pluginconvertwordpress.git

# 7. Subir a GitHub
git push -u origin main

# ============================================
# Para futuros commits:
# ============================================

# Ver cambios
git status

# Añadir archivos específicos
git add archivo.txt
git add backend/
git add .

# Ver diffs
git diff
git diff --staged

# Crear commit
git commit -m "Descripción del cambio"

# Subir cambios
git push origin main

# ============================================
# Comandos útiles:
# ============================================

# Ver historial
git log --oneline

# Crear rama
git checkout -b feature/nueva-feature

# Cambiar de rama
git checkout main

# Merge de ramas
git merge feature/nueva-feature

# Ver remote
git remote -v

# Actualizar desde remote
git pull origin main
