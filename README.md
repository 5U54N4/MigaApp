# Documentación Sistema para Panadería

## Descripción
El sistema desarrollado para la panadería está diseñado para facilitar la gestión de productos y ventas, proporcionando una interfaz intuitiva y funcionalidades específicas según el tipo de usuario. El sistema cuenta con dos tipos de usuarios: Administrador y Empleado.

## Arquitectura usada
- Windows 11

## Requerimientos
Este proyecto requiere la instalación de los siguientes componentes para su correcta ejecución:
- IIS (Internet Information Services)
- PHP
- Microsoft SQL Server
- Navegador web

### Enlaces de descarga:
- **IIS**: Incluido como característica opcional en Windows. Se puede habilitar desde "Activar o desactivar características de Windows".
- **PHP para IIS**: [Descargar desde windows.php.net](https://windows.php.net/) (se recomienda la versión 7.4 o superior)
- **Microsoft SQL Server**: [Descargar desde Microsoft](https://www.microsoft.com/es-es/sql-server/)
- **Microsoft Drivers for PHP for SQL Server**: [Descargar drivers](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)

## Bibliotecas opcionales
El sistema utiliza las siguientes bibliotecas y recursos en su interfaz web:
- **Bootstrap 5.3.6**: [getbootstrap.com](https://getbootstrap.com)
- **SweetAlert2**: [sweetalert2.github.io](https://sweetalert2.github.io/)
- **HTML5 y CSS3**
- **JQuery**: [CDN](https://cdnjs.com/libraries/jquery)
- **DataTables** (dataTables.min.js)
- **DataTables + Bootstrap 5 Estilos** (dataTables.bootstrap5.min.js)
- **Html2Pdf**: [Descargar](https://ehvaldepenia.com/temp/html2pdf.zip)

## Instalación
1. Coloque los archivos del proyecto dentro de la carpeta raíz del sitio en IIS.
2. Configure los datos de conexión a la base de datos dentro del archivo `MySQLAux.php`.
3. Verifique que los drivers de conexión para PHP y SQL Server estén correctamente habilitados en el archivo `php.ini`.
4. Se recomienda el uso de un editor como Visual Studio Code.

## Disponibilidad
La versión más reciente de este paquete está disponible en:
[GitHub - PanaderiaDelicia](https://github.com/SoriaCode/PanaderiaDelicia)

## Autores
- **Soria Cruz Salvador Giovanni** (ssoriac2200@alumno.ipn.mx)
- **Lozano Clemente Susana** (slozanoc2200@alumno.ipn.mx)
- **Cruz Martínez Aleitia Yari** (acruzm2212@alumno.ipn.mx)

## Diagrama
```mermaid
erDiagram
    Usuario {
        int Pk_usuario_id
        string usuario_nombre
        string usuario_clave
        string tipo_usuario
    }
    Ventas {
        int Pk_id_venta
        int Fk_id_empleado
        date fecha_venta
        float totalventa
    }
    Panes {
        int Pk_id_pan
        string nombre
        float precio_unitario
        string descripcion
    }

    Usuario ||--o{ Ventas : "realiza"
    Ventas }o--|| Panes : "incluye"
