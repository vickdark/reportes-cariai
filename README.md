# Reportes Cariai

Reportes Cariai es una aplicación web basada en Laravel diseñada para proporcionar reportes completos y análisis de ventas. Ofrece un panel de control (dashboard) para monitorear los Indicadores Clave de Rendimiento (KPIs) y hacer seguimiento del rendimiento de ventas a través de diferentes canales, países y segmentos de clientes.

## Características

- **Dashboard de Ventas**: Visualiza KPIs críticos incluyendo Total de Órdenes, Órdenes Pagadas, Órdenes Pendientes, Ingresos Totales, Valor Promedio de Orden y Clientes Únicos.
- **Comparativa de Períodos**: Compara automáticamente las métricas actuales con períodos anteriores para mostrar el porcentaje de crecimiento o decrecimiento.
- **Filtros Avanzados**: Filtra los datos de ventas por:
  - Rangos de fechas
  - Estado (Pagada, Pendiente, Cancelada)
  - Canal de Venta (Web, API, Teléfono, Correo, WhatsApp)
  - País (Colombia, México, Chile, Argentina, Perú)
  - Segmento de Cliente (Pyme, Mediana, Enterprise)
- **Exportación de Datos**: Exporta los reportes de ventas filtrados a formato CSV para un análisis más profundo.
- **Tabla de Datos Detallada**: Explora registros de ventas individuales con paginación, ordenamiento y búsqueda.
- **Alertas**: Alertas integradas para métricas importantes, como altas tasas de cancelación o alertas de órdenes antiguas aún pendientes.

## Tecnologías

- **Framework**: [Laravel 12](https://laravel.com)
- **Lenguaje**: PHP 8.2+
- **Frontend**: 
  - [Bootstrap](https://getbootstrap.com/) (Diseño e Interfaz)
  - [Chart.js](https://www.chartjs.org/) (Gráficos interactivos)
  - [Grid.js](https://gridjs.io/) (Tablas de datos)
  - [Vite](https://vitejs.dev/) (Empaquetador de assets)
- **Base de Datos**: MySQL

---

## 🚀 Guía Paso a Paso: Ejecución en Local (Windows)

Esta guía asume que estás usando **Windows** y te recomienda usar **Laravel Herd** para tener el entorno de PHP listo de forma rápida, además de **MySQL** para la base de datos.

### 1. ¿Qué debes tener a la mano?

Antes de empezar, asegúrate de tener instalado lo siguiente en tu máquina Windows:

1. **[Laravel Herd para Windows](https://herd.laravel.com/windows)**: Esto instalará automáticamente PHP, Composer y configurará un entorno de desarrollo local sin complicaciones.
2. **[Node.js](https://nodejs.org/es/)**: Necesario para compilar los recursos del frontend (JavaScript y CSS). Descarga la versión LTS.
3. **[Git](https://git-scm.com/download/win)**: Para poder clonar el repositorio.
4. **MySQL**: Puedes instalarlo a través de herramientas como [DBngin](https://dbngin.com/) (si estuviera disponible), [XAMPP](https://www.apachefriends.org/es/index.html), [MySQL Installer oficial](https://dev.mysql.com/downloads/installer/), o usar un cliente de bases de datos como [HeidiSQL](https://www.heidisql.com/) o [TablePlus](https://tableplus.com/) para gestionar tus bases de datos.

### 2. Clonar el repositorio

Abre tu terminal (por ejemplo, PowerShell o la terminal de Herd) y navega a la carpeta de Herd donde guardarás tus proyectos (usualmente `C:\Users\TuUsuario\Herd`).

```bash
git clone <URL_DEL_REPOSITORIO> reportes-cariai
cd reportes-cariai
```

### 3. Crear la Base de Datos MySQL

Abre tu gestor de base de datos MySQL favorito (HeidiSQL, DBeaver, phpMyAdmin, etc.) y crea una base de datos vacía. Por ejemplo, nómbrala: `reportes_cariai`.

### 4. Configurar el entorno (.env)

Copia el archivo de configuración de ejemplo para crear el tuyo propio:

```bash
cp .env.example .env
```

Abre el archivo `.env` en tu editor de código (como VS Code) y actualiza la sección de base de datos para que apunte a tu MySQL local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reportes_cariai
DB_USERNAME=root       # Tu usuario de MySQL
DB_PASSWORD=           # Tu contraseña de MySQL (déjalo vacío si no tiene)
```

### 5. Instalar dependencias y preparar la base de datos

Con Herd corriendo, ejecuta los siguientes comandos en tu terminal dentro de la carpeta del proyecto:

1. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```
2. **Generar la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```
3. **Ejecutar las migraciones:**
   Esto creará todas las tablas necesarias en tu base de datos MySQL.
   ```bash
   php artisan migrate
   ```

### 6. Instalar dependencias de Frontend y Compilar

Ahora necesitamos preparar los estilos (Tailwind CSS) y el JavaScript:

```bash
npm install
npm run build
```

### 7. ¡Ver el proyecto en el navegador!

Si estás usando **Laravel Herd**, el proyecto debería estar automáticamente disponible en tu navegador en:

👉 **[http://reportes-cariai.test](http://reportes-cariai.test)**

*(Nota: Herd mapea automáticamente las carpetas en tu directorio de Herd a dominios `.test`)*.

Si por alguna razón necesitas correr el servidor manualmente, puedes ejecutar:

```bash
composer dev
```
*(Este comando iniciará el servidor de Laravel, la cola de trabajos y el servidor de desarrollo de Vite al mismo tiempo).*

---

## Estructura Principal del Proyecto

- **Controladores**: La lógica principal de los reportes se encuentra en `App\Http\Controllers\SoporteReportController`.
- **Rutas**: Las rutas web están definidas en `routes/web.php`, principalmente mapeadas bajo `/reportes/ventas`.
- **Base de Datos**: Las migraciones estructuran tablas como `customers`, `products`, `sales`, y `sale_items`.

## Licencia

Este proyecto es software de código abierto bajo la [licencia MIT](https://opensource.org/licenses/MIT).
