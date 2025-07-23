# 📊 DIAGRAMA DEL SISTEMA DE VENDEDOR

## 🏗️ ARQUITECTURA GENERAL

```
┌─────────────────────────────────────────────────────────────┐
│                    SISTEMA DE VENDEDOR                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │   VISTA     │    │ CONTROLADOR │    │   MODELO    │     │
│  │  (Frontend) │◄──►│ (Lógica)    │◄──►│ (Datos)     │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 📁 ESTRUCTURA DE CARPETAS

```
vendedor-php/
├── 📁 config/
│   ├── 📄 app.php
│   ├── 📄 database.php
│   └── 📄 errors.php
│
├── 📁 controlador/
│   ├── 📄 ControladorAuth.php
│   ├── 📄 ControladorIndex.php
│   ├── 📄 ControladorInventario.php
│   ├── 📄 ControladorMenuPrincipal.php
│   ├── 📄 ControladorPedidos.php
│   ├── 📄 ControladorProductos.php
│   └── 📄 ControladorVentas.php
│
├── 📁 modelo/
│   ├── 📄 ModeloCliente.php
│   ├── 📄 ModeloPedido.php
│   ├── 📄 ModeloProducto.php
│   ├── 📄 ModeloUsuario.php
│   └── 📄 ModeloVenta.php
│
├── 📁 vista/
│   ├── 📄 index.php (Página principal)
│   ├── 📄 login.php
│   ├── 📄 menu_principal.php
│   ├── 📄 productos.php
│   ├── 📄 ventas.php
│   ├── 📄 pedidos.php
│   ├── 📄 clientes.php
│   ├── 📄 inventario.php
│   └── 📄 logout.php
│
├── 📁 css/
│   └── 📄 style.css
│
├── 📁 js/
│   └── 📄 app.js
│
└── 📁 sql/
    ├── 📄 crear_tablas_ventas.sql
    └── 📄 crear_tabla_usuarios.sql
```

## 🔄 FLUJO DE NAVEGACIÓN

```
┌─────────────┐
│   LOGIN     │
│  (login.php)│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   INDEX     │
│ (index.php) │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────────────────────────┐
│                    MENÚ PRINCIPAL                           │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   VENTAS    │  │  PEDIDOS    │  │  CLIENTES   │         │
│  │ (ventas.php)│  │(pedidos.php)│  │(clientes.php)│         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ PRODUCTOS   │  │ INVENTARIO  │  │   LOGOUT    │         │
│  │(productos.php)│ │(inventario.php)│ │ (logout.php) │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

## 🗄️ BASE DE DATOS

```
┌─────────────────────────────────────────────────────────────┐
│                    BASE DE DATOS: gardelcatalogo           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │   usuario   │    │  productos  │    │   cliente   │     │
│  │             │    │             │    │             │     │
│  │ • id        │    │ • id        │    │ • id        │     │
│  │ • nombre    │    │ • nombre    │    │ • nombre    │     │
│  │ • correo    │    │ • precio    │    │ • telefono  │     │
│  │ • contraseña│    │ • tipo      │    │ • correo    │     │
│  │ • rol       │    │ • stock     │    │ • direccion │     │
│  └─────────────┘    └─────────────┘    └─────────────┘     │
│                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐     │
│  │productos_   │    │inventario_  │    │  colores    │     │
│  │ventas       │    │bodega       │    │             │     │
│  │             │    │             │    │ • id        │     │
│  │ • id        │    │ • id        │    │ • nombre    │     │
│  │ • cliente_id│    │ • producto_id│   │ • codigo_hex│     │
│  │ • factura   │    │ • stock     │    └─────────────┘     │
│  │ • total     │    │ • lote      │                        │
│  │ • fecha     │    └─────────────┘    ┌─────────────┐     │
│  └─────────────┘                       │   tallas    │     │
│                                        │             │     │
│                                        │ • id        │     │
│                                        │ • nombre    │     │
│                                        │ • categoria │     │
│                                        └─────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

## 🔐 SISTEMA DE AUTENTICACIÓN

```
┌─────────────┐
│   LOGIN     │
│  (login.php)│
└──────┬──────┘
       │ POST
       ▼
┌─────────────┐
│Controlador  │
│   Auth      │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ModeloUsuario│
│ autenticar()│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   SESSION   │
│  Variables  │
│             │
│ • usuario_id│
│ • usuario_  │
│   nombre    │
│ • usuario_  │
│   email     │
│ • usuario_  │
│   rol       │
└─────────────┘
```

## 🎨 INTERFAZ DE USUARIO

```
┌─────────────────────────────────────────────────────────────┐
│                    NAVBAR                                   │
├─────────────────────────────────────────────────────────────┤
│ 🏪 Sistema de Vendedor  │ Menú │ Ventas │ Pedidos │ Clientes│
│                         │      │        │         │         │
│                         │ Productos │ Inventario │ 👤 Usuario│
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    CONTENIDO PRINCIPAL                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   TOTAL     │  │   TOTAL     │  │  CABALLEROS │         │
│  │ PRODUCTOS   │  │  CLIENTES   │  │             │         │
│  │    150      │  │     25      │  │     45      │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │    DAMAS    │  │ PRODUCTOS   │  │  ACCIONES   │         │
│  │             │  │ RECIENTES   │  │  RÁPIDAS    │         │
│  │     35      │  │             │  │             │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 TECNOLOGÍAS UTILIZADAS

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3.0
- **Iconos**: Font Awesome 6.0.0
- **Patrón**: MVC (Modelo-Vista-Controlador)

## 📋 FUNCIONALIDADES PRINCIPALES

1. **🔐 Autenticación de Usuarios**
   - Login/Logout
   - Control de sesiones
   - Roles (admin/vendedor)

2. **📦 Gestión de Productos**
   - Listar productos
   - Agregar/Editar productos
   - Control de inventario

3. **🛒 Sistema de Ventas**
   - Crear nuevas ventas
   - Generar facturas
   - Historial de ventas

4. **👥 Gestión de Clientes**
   - Registro de clientes
   - Historial de compras
   - Información de contacto

5. **📊 Dashboard**
   - Estadísticas generales
   - Productos recientes
   - Acciones rápidas

## 🚀 INSTALACIÓN Y CONFIGURACIÓN

1. **Base de Datos**: Ejecutar `sql/crear_tablas_ventas.sql`
2. **Usuarios**: Ejecutar `sql/crear_tabla_usuarios.sql`
3. **Configuración**: Editar `config/database.php`
4. **Servidor**: Configurar XAMPP/WAMP
5. **Acceso**: http://localhost/vendedor-php/

## 🔍 CREDENCIALES DE PRUEBA

- **Email**: vendedor@demo.com
- **Contraseña**: 123456
- **Rol**: Vendedor

- **Email**: admin@demo.com  
- **Contraseña**: admin123
- **Rol**: Administrador 