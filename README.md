# Bienes Raíces y Vehículos LAV

Aplicación web independiente para crear tarjetas digitales móviles de propiedades y vehículos.

## Incluye
- Editor para tarjetas de bienes raíces y automotores.
- Campos opcionales para precio, ubicación, descripción y especificaciones.
- Fotos cargadas desde el dispositivo, portada y galería horizontal deslizable.
- Cuatro estilos visuales y 100 variaciones numeradas de plantilla.
- Contacto con teléfono, WhatsApp, correo, Facebook, Instagram, TikTok, Facebook Marketplace, Mercado Libre y Google Maps.
- Guardado local de borradores en el navegador.
- Publicación de tarjetas en Hostinger con enlaces cortos y datos guardados en el servidor.
- Compartir tarjeta mediante el menú nativo del teléfono, compartir la imagen PNG y copiarla al portapapeles cuando el navegador lo permite.
- Guardar contacto mediante archivo vCard y menú para compartir compatible con teléfonos.
- PWA instalable con manifest y service worker para abrirla como aplicación desde la pantalla de inicio.
- Diseño adaptable a celular, tableta y computadora.

## Dónde se guarda cada cosa
- **Guardar tarjeta:** conserva un borrador en el almacenamiento local del navegador y dispositivo.
- **Crear enlace para compartir:** publica una copia de la tarjeta en `api/data/` dentro del hosting y genera un enlace corto con identificador.
- Los archivos de tarjetas publicados se sirven a través de `api/get-card.php`; el acceso web directo a la carpeta de datos está bloqueado por `.htaccess`.
- Las tarjetas publicadas permanecen en el hosting hasta que se eliminen del servidor. No guardes información confidencial que no quieras compartir con quien reciba el enlace.

## Publicación
La aplicación usa PHP en Hostinger para guardar y recuperar tarjetas publicadas. El directorio `api/data/` se crea automáticamente cuando se publica la primera tarjeta.

## Publicar en Hostinger
Destino previsto: `public_html/bienes-raices-vehiculos/`.

### Publicación automática por FTP con GitHub Actions
1. En Hostinger, abre la información de FTP de tu sitio y crea/usa un usuario FTP con acceso al sitio.
2. En GitHub, abre **Settings → Secrets and variables → Actions → New repository secret**.
3. Agrega estos secretos (usa los valores exactos de Hostinger):
   - `HOSTINGER_FTP_SERVER`: host FTP de Hostinger (no inventar; cópialo del panel).
   - `HOSTINGER_FTP_USERNAME`: usuario FTP.
   - `HOSTINGER_FTP_PASSWORD`: contraseña FTP.
4. Verifica que el directorio destino exista en Hostinger: `public_html/bienes-raices-vehiculos/`.
5. Después de guardar los secretos, abre **Actions** y ejecuta el workflow **Deploy to Hostinger** con **Run workflow**, o haz un nuevo commit a `main`.
6. Comprueba la URL: https://lavdigitalmkt.com/bienes-raices-vehiculos/

No pongas credenciales FTP en archivos del repositorio ni en mensajes. El workflow solo sube archivos del repositorio a la carpeta destino indicada; no elimina archivos remotos que no estén en el repositorio.

## Probar en local
Abre `index.html` en un navegador moderno. Algunas funciones de compartir/copiar requieren HTTPS o localhost.
