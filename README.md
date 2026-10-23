# Bienes Raíces y Vehículos LAV

Aplicación web independiente para crear tarjetas digitales móviles de propiedades y vehículos.

## Incluye
- Editor para tarjetas de bienes raíces y automotores.
- Campos opcionales para precio, ubicación, descripción y especificaciones.
- Fotos cargadas desde el dispositivo, portada y galería horizontal deslizable.
- Cuatro estilos visuales y 100 variaciones numeradas de plantilla.
- Contacto con teléfono, WhatsApp, correo, Facebook, Instagram, TikTok, Facebook Marketplace, Mercado Libre y Google Maps.
- Exportación de contacto a vCard (.vcf).
- Guardado local en el navegador y enlace compartible con datos incluidos en el fragmento de URL.
- Diseño adaptable a celular, tableta y computadora.

## Importante: límites de esta primera versión
Esta es una aplicación estática, sin servidor ni base de datos. Las tarjetas guardadas desde el botón **Guardar tarjeta** quedan en el navegador y dispositivo donde se crearon. Los enlaces compartidos codifican los datos en la propia URL; si se agregan muchas fotos, el enlace puede quedar demasiado largo para algunas aplicaciones o códigos QR. No se deben usar como almacenamiento permanente ni incluir datos confidenciales. Para almacenamiento central, enlaces cortos y administración de tarjetas desde varios dispositivos, se requiere conectar una base de datos/backend.

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
