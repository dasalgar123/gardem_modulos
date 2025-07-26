# Sistema de Administración PHP

Sistema de administración web desarrollado en PHP con arquitectura MVC, completamente traducido al español.

## 🚀 Características

- **Arquitectura MVC** completa
- **Interfaz en español** completamente localizada
- **Sistema de autenticación** seguro
- **Gestión de usuarios, clientes, productos y pedidos**
- **Inventario y control de stock**
- **Entradas, salidas, devoluciones y garantías**
- **Diseño responsive** para todos los dispositivos
- **Modo oscuro** integrado

## 📁 Estructura del Proyecto

```
php-admin/
├── config/                          # Configuración
│   └── database.php                 # Conexión a base de datos
├── controlador/                     # Controladores MVC
│   ├── ControladorMenuPrincipalPrincipal.php
│   ├── ControladorUsuarios.php
│   ├── ControladorClientes.php
│   ├── ControladorProductos.php
│   ├── ControladorPedidos.php
│   ├── ControladorInventario.php
│   ├── ControladorEntradas.php
│   ├── ControladorSalidas.php
│   ├── ControladorDevoluciones.php
│   ├── ControladorGarantias.php
│   ├── ControladorTraslados.php
│   ├── ControladorProveedores.php
│   ├── ControladorVentas.php
│   ├── LoginController.php
│   └── LogoutController.php
├── modelo/                          # Modelos de datos
│   ├── modelo_Autenticacion.php
│   ├── modelo_Usuario.php
│   ├── modelo_Cliente.php
│   ├── modelo_Producto.php
│   └── modelo_Pedido.php
├── vista/                           # Vistas
│   ├── vista_menu_principal.php
│   ├── vista_usuarios.php
│   ├── vista_clientes.php
│   ├── vista_products.php
│   ├── vista_orders.php
│   ├── vista_inventario.php
│   ├── vista_entradas.php
│   ├── vista_salidas.php
│   ├── vista_devoluciones.php
│   ├── vista_garantias.php
│   ├── vista_traslados.php
│   ├── vista_proveedores.php
│   ├── vista_ventas.php
│   └── vista_login.php
├── css/                             # Estilos CSS
│   ├── style.css
│   ├── barra-lateral.css
│   ├── menu_principal.css
│   ├── usuarios.css
│   ├── clientes.css
│   ├── products.css
│   ├── inventario.css
│   ├── entradas.css
│   ├── salidas.css
│   ├── devoluciones.css
│   ├── garantias.css
│   ├── traslados.css
│   ├── proveedores.css
│   └── ventas.css
├── js/                              # JavaScript
│   ├── script.js
│   ├── usuarios.js
│   ├── clientes.js
│   ├── inventario.js
│   └── proveedores.js
├── menu/                            # Contenido de menús
│   ├── menu_menu_principal.php
│   ├── menu_usuarios.php
│   ├── menu_clientes.php
│   ├── menu_productos.php
│   ├── menu_ordenes.php
│   ├── menu_inventario.php
│   ├── menu_entradas.php
│   ├── menu_salidas.php
│   ├── menu_devoluciones.php
│   ├── menu_garantias.php
│   ├── menu_traslados.php
│   ├── menu_proveedores.php
│   └── menu_ventas.php
└── index.php                        # Punto de entrada
```

## 🎯 Módulos Disponibles

### 🔐 Autenticación
- Login seguro con validación
- Control de sesiones
- Logout automático

### 👥 Gestión de Usuarios
- Lista de usuarios
- Crear, editar, eliminar usuarios
- Control de roles y permisos

### 👥 Gestión de Clientes
- Registro de clientes
- Información de contacto
- Historial de compras

### 📦 Gestión de Productos
- Catálogo de productos
- Control de stock
- Gestión de precios

### 🛒 Gestión de Pedidos
- Crear y gestionar pedidos
- Seguimiento de estados
- Información de clientes

### 📊 Inventario
- Control de stock en tiempo real
- Alertas de stock bajo
- Reportes de inventario

### 📥 Entradas
- Registro de entradas de productos
- Control de proveedores
- Documentación de entradas

### 📤 Salidas
- Registro de salidas de productos
- Control de ventas
- Documentación de salidas

### 🔄 Devoluciones
- Gestión de devoluciones
- Control de calidad
- Procesos de reembolso

### 🛡️ Garantías
- Gestión de garantías
- Control de fechas
- Seguimiento de reparaciones

### 🔄 Traslados
- Movimientos entre bodegas
- Control de ubicaciones
- Documentación de traslados

### 🚚 Proveedores
- Gestión de proveedores
- Información de contacto
- Historial de compras

### 💰 Ventas
- Registro de ventas
- Control de ingresos
- Reportes de ventas

## 🚀 Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/dasalgar123/php-admin-system.git
   ```

2. **Configurar servidor web**
   - Copiar archivos a `htdocs/` (XAMPP) o directorio web
   - Configurar base de datos en `config/database.php`

3. **Configurar base de datos**
   - Crear base de datos MySQL
   - Importar estructura de tablas
   - Configurar credenciales en `config/database.php`

4. **Acceder al sistema**
   - Abrir navegador en `http://localhost/php-admin`
   - Usar credenciales de administrador

## 🔧 Configuración

### Base de Datos
Editar `config/database.php`:
```php
$host = 'localhost';
$dbname = 'php_admin';
$username = 'tu_usuario';
$password = 'tu_password';
```

### Personalización
- Colores: Editar variables CSS en `css/style.css`
- Idiomas: Modificar textos en archivos de vista
- Módulos: Agregar nuevos controladores y vistas

## 🛡️ Seguridad

- ✅ Autenticación con sesiones PHP
- ✅ Validación de formularios
- ✅ Escape de datos HTML
- ✅ Protección contra XSS básica

### Recomendaciones para Producción
- 🔒 Usar HTTPS
- 🔒 Implementar hash de contraseñas
- 🔒 Configurar base de datos segura
- 🔒 Implementar CSRF tokens
- 🔒 Validación del lado servidor
- 🔒 Logs de auditoría

## 📱 Responsive Design

- **Desktop**: Sidebar fijo con navegación completa
- **Tablet**: Sidebar colapsible
- **Mobile**: Navegación optimizada

## 🌙 Modo Oscuro

El sistema incluye modo oscuro con:
- Cambio instantáneo entre temas
- Persistencia automática
- Detección de preferencia del sistema
- Atajos de teclado

## 🔄 Actualizaciones Recientes

- ✅ Traducción completa al español
- ✅ Renombrado de archivos y clases
- ✅ Eliminación de archivos no utilizados
- ✅ Mejoras en la estructura MVC
- ✅ Optimización de código
- ✅ Limpieza de CSS duplicado

## 📊 Estadísticas del Proyecto

- **PHP**: 61.4%
- **CSS**: 19.1%
- **JavaScript**: 11.1%
- **HTML**: 8.4%

## 🤝 Contribución

1. Fork el proyecto
2. Crear rama para feature (`git checkout -b feature/NuevaFuncionalidad`)
3. Commit cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/NuevaFuncionalidad`)
5. Abrir Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

## 📞 Soporte

Para soporte técnico:
- Revisar documentación
- Verificar logs de errores
- Contactar al equipo de desarrollo

---

**Nota**: Sistema de administración PHP con arquitectura MVC implementada. Para uso en producción, implementar todas las medidas de seguridad recomendadas. 