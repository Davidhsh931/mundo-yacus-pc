# Mundo Yacus-PC

![Laravel](https://img.shields.io/badge/Laravel-10-red)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-blue)
![OpenAI](https://img.shields.io/badge/OpenAI-API-purple)

> Sistema agropecuario moderno con IA clasificadora inteligente para gestión de productos, pedidos y eventos.

## **Características Principales**

### **IA Clasificadora**
- **OpenAI GPT-3.5 Turbo** para clasificación automática de productos
- **Jerarquía inteligente**: Nombre > Descripción > Ficha técnica
- **Panel de entrenamiento** dinámico sin tocar código
- **Sistema de fallback** automático

### **Gestión de Productos**
- **CRUD completo** con imágenes múltiples
- **Especificaciones técnicas** en formato JSON
- **Control de stock** y activación
- **Categorías dinámicas** con training data

### **Panel de Administración**
- **Dashboard analytics** con gráficos
- **Gestión de pedidos** y estados
- **Sistema de eventos** y calendario
- **Entrenamiento IA** en tiempo real

### **Experiencia de Usuario**
- **Diseño responsive** mobile-first
- **UX premium** con animaciones suaves
- **Componentes reutilizables** Vue 3
- **Navegación intuitiva** con breadcrumbs

## **Tech Stack**

### **Backend**
- **Laravel 10** - Framework PHP
- **PHP 8+** - Lenguaje principal
- **MySQL** - Base de datos
- **OpenAI API** - Clasificación IA
- **Eloquent ORM** - Modelos de datos

### **Frontend**
- **Vue 3** - Framework JavaScript
- **Inertia.js** - SPA sin API separada
- **TailwindCSS** - Framework CSS
- **Vite** - Build tool
- **Componentes personalizados**

### **Infraestructura**
- **Docker** - Contenerización
- **Docker Compose** - Orquestación
- **Nginx** - Servidor web
- **PHP-FPM** - Procesamiento PHP

## **Instalación**

### **Requisitos Previos**
- PHP 8.1+
- Composer
- Node.js 16+
- MySQL 8.0+
- Docker (opcional)

### **Pasos de Instalación**

1. **Clonar el repositorio**
```bash
git clone https://github.com/Davidhsh931/mundo-yacus-pc.git
cd mundo-yacus-pc
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mundo_yacus
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=your_openai_api_key_here
```

5. **Migrar y seedear**
```bash
php artisan migrate
php artisan db:seed
```

6. **Compilar assets**
```bash
npm run build
```

7. **Iniciar servidor**
```bash
php artisan serve
```

### **Instalación con Docker**

```bash
docker-compose up -d
docker-compose exec php bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve --host=0.0.0.0
```

## **Configuración de IA**

### **OpenAI API**
1. Obtener API key en [OpenAI Platform](https://platform.openai.com/)
2. Configurar en `.env`:
```env
OPENAI_API_KEY=sk-your-api-key-here
```

### **Entrenamiento de Categorías**
1. Acceder a `/admin/ai-training`
2. Editar palabras clave por categoría
3. Sincronizar modelo con "SINCRONIZAR MODELO"

## **Estructura del Proyecto**

```
mundo-yacus-pc/
|
app/
|-- Http/Controllers/
|   |-- Admin/
|   |   |-- GuineaPigAdminController.php  # CRUD + IA
|   |   |-- AiTrainingController.php      # Entrenamiento IA
|   |   |-- OrdersController.php          # Pedidos
|   |   |-- EventsController.php          # Eventos
|   |-- Auth/                            # Autenticación
|
resources/js/
|-- Pages/
|   |-- Admin/
|   |   |-- GuineaPigs/Index.vue          # Listado productos
|   |   |-- AiTraining.vue                # Panel IA
|   |   |-- CreateProduct.vue             # Crear productos
|   |   |-- EditPig.vue                   # Editar productos
|   |   |-- Orders/Index.vue              # Pedidos
|   |   |-- Events/Index.vue              # Eventos
|   |-- Auth/                             # Autenticación
|
database/
|-- migrations/                           # Estructura BD
|-- seeders/                             # Datos iniciales
|
storage/
|-- app/public/images/                   # Imágenes productos
```

## **Funcionalidades Detalladas**

### **Clasificación IA**
- **Análisis jerárquico**: Prioridad al nombre del producto
- **Contexto secundario**: Descripción y especificaciones
- **Reglas dinámicas**: Training data editable
- **Fallback automático**: Primera categoría disponible

### **Gestión de Categorías**
- **Animales**: Productos vivos (cuyes, gallinas, etc.)
- **Procesados**: Carnes y derivados (chicharrón, charqui, etc.)
- **Otros**: Forraje, herramientas, insumos (alfalfa, monturas, etc.)

### **Sistema de Imágenes**
- **Upload múltiple** con drag & drop
- **Optimización automática** de tamaño
- **Gestión de almacenamiento** con Storage facade
- **Eliminación automática** al borrar producto

## **API Endpoints**

### **Productos**
- `GET /admin/guinea-pigs` - Listar productos
- `POST /admin/guinea-pigs` - Crear producto
- `PUT /admin/guinea-pigs/{id}` - Actualizar producto
- `DELETE /admin/guinea-pigs/{id}` - Eliminar producto

### **Categorías (IA Training)**
- `GET /admin/ai-training` - Listar categorías
- `POST /admin/ai-training` - Crear categoría
- `PUT /admin/ai-training/{id}` - Actualizar training data
- `DELETE /admin/ai-training/{id}` - Eliminar categoría

## **Testing**

### **Ejecutar Tests**
```bash
php artisan test
```

### **Tests Disponibles**
- Autenticación de usuarios
- CRUD de productos
- Clasificación IA
- Gestión de categorías

## **Deployment**

### **Producción**
1. Configurar variables de entorno
2. Optimizar aplicación:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
3. Setear permisos:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### **Docker Production**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

## **Contribuir**

1. Fork del proyecto
2. Crear feature branch: `git checkout -b feature/amazing-feature`
3. Commit cambios: `git commit -m 'Add amazing feature'`
4. Push: `git push origin feature/amazing-feature`
5. Pull Request

## **Licencia**

Este proyecto está bajo licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

## **Autor**

- **David H.** - *Desarrollador principal* - [Davidhsh931](https://github.com/Davidhsh931)

## **Agradecimientos**

- **Laravel Team** - Framework increíble
- **Vue.js Team** - Framework frontend reactivo
- **OpenAI** - API de clasificación
- **TailwindCSS** - Framework CSS utilitario

---

**Mundo Yacus-PC** © 2024 - Sistema agropecuario moderno con IA integrada
